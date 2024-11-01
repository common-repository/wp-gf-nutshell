<?php

class GravityNutshellSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks.
     */
    private $options;
    private $name;
    private static $id;
    private $labels;
    private $tags;
    private $customFields;


    /**
     * Start up.
     */
    public function __construct($name)
    {
        global $gravity_forms;

        if (class_exists('GFCommon')) {
            $gravity_forms = Controllers\GravityFormsController::getInstance();

            add_action('admin_menu', array($this, 'add_plugin_page'));
            add_action('admin_init', array($this, 'page_init'));
            add_action('admin_init', array($this, 'setApiUsers'));
            add_action('admin_init', array($this, 'setApiTags'));
            $this->name = $name;
            $this->customFields = $gravity_forms->findCustomFields();
        }
    }

    /**
     * Add options page.
     */
    public function add_plugin_page()
    {
        add_options_page(
            'Settings Admin',
            $this->name,
            'manage_options',
            'gravityforms-nutshell-integration',
            array($this, 'create_admin_page')
        );
    }

    /**
     * Options page callback.
     */
    public function create_admin_page()
    {
        ?>
<div class="wrap">
    <?php echo '<h4>'.$this->name.' '.'Settings</h4>'; ?>
    <form id="wp_gf_options_settings" class="gf_nutshell_options" method="post" action="options.php">
        <?php
                settings_fields('my_option_group');
        do_settings_sections('wp-gf-nutshell-admin');
        echo '<div id="wp-gf-notification"></div>';

        $other_attributes = array( 'id' => 'wp-gf-submit-button-id' );
        submit_button(__('Save Settings'), 'primary', 'wp-gf-nutshell-save-settings', true, $other_attributes); ?>
    </form>
</div>
<?php
    }

    /**
     * Register and add settings.
     */
    public function page_init()
    {
        $forms = GFAPI::get_forms();
        $api_fields = [];

        register_setting(
            'my_option_group', // Option group
            'nutshell_api_username', // Option name
            array($this, 'sanitize_email') // Sanitize
        );

        register_setting(
            'my_option_group', // Option group
            'nutshell_api_key', // Option name
            array($this, 'sanitize_apikey') // Sanitize
        );

        add_settings_field(
            'nutshell_api_username',
            'Enter API username',
            array($this, 'user_callback'),
            'wp-gf-nutshell-admin',
            'creds',
            array('title' => 'nutshell_api_username')
        );

        add_settings_field(
            'nutshell_api_key',
            'Enter API key',
            array($this, 'api_callback'),
            'wp-gf-nutshell-admin',
            'creds',
            array('title' => 'nutshell_api_key')
        );

        add_settings_section(
            'creds', // ID,
                'API info',
            array($this, 'print_user_info'), // Callback
                'wp-gf-nutshell-admin'
        );

        foreach ($forms as $form) {
            add_settings_section(
                $form['title'], // ID
                $form['title'], // Title
                array($this, 'print_section_info'), // Callback
                'wp-gf-nutshell-admin'
            );

            $form_title = $this->cleanFormTitle($form['title']);

            foreach ($form['fields'] as $field) {
                if (!empty($field->label)) {
                    $option_name = str_replace(' ', '_', $field->label);
                    $option_name = strtolower($option_name);
                    $option_name .= '_'.$form_title;
                    $form_labels[] = $option_name;

                    add_settings_field(
                        $form_title,
                        'Select a Nutshell user to associate with the form',
                        array($this, 'dropdown_option_users_callback'),
                        'wp-gf-nutshell-admin',
                        $form['title'],
                        array('title' => $form_title, 'field' => 'api_users')
                    );

                    register_setting(
                        'my_option_group', // Option group
                        'dropdown_option_setting_api_users_'.$form_title.'_api_users'
                    );

                    add_settings_field(
                        $form_title.'_tags',
                        'Select a tag for this form',
                        array($this, 'dropdown_option_tags_callback'),
                        'wp-gf-nutshell-admin',
                        $form['title'],
                        array('label' => $form_title, 'field' => '_api_tags')
                    );

                    register_setting(
                        'my_option_group',
                        'dropdown_option_setting_tag_name_'.$form_title.'_api_tags'
                    );

                    add_settings_field(
                        $option_name,
                        $field->label,
                        array($this, 'dropdown_option_nutshell_callback'),
                        'wp-gf-nutshell-admin',
                        $form['title'],
                        array('label' => $form_title, 'field' => $option_name)
                    );

                    register_setting(
                        'my_option_group',
                        'dropdown_option_setting_option_name_'.$form_title.'_'.$option_name
                    );
                }
            }
        }
    }

    public function sanitize_email($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $input;
        }

        return;
    }

    public function sanitize_email_forms($input)
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return $input;
        } else {
            return 'Please enter a valid email';
        }
    }

    public function sanitize_apikey($input)
    {
        $clean = '';

        if ($clean = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH)) {
            return $clean;
        }
    }

    public function sanitize_tags_forms($input)
    {
        $clean = '';

        if ($clean = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH)) {
            return $clean;
        }
    }

    public function user_callback($args)
    {
        $current_option = $input_text = '';
        $current_option = get_option($args['title']);
        $args['value'] = filter_var($current_option, FILTER_VALIDATE_EMAIL);

        $this->print_text_input($args);
    }

    public function api_callback($args)
    {
        $current_option = $input_text = $clean = '';
        $current_option = get_option($args['title']);
        $args['value'] = filter_var($current_option, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

        $this->print_text_input($args);
    }

    public function dropdown_option_nutshell_callback($args)
    {
        $the_option = 'dropdown_option_setting_option_name_'.$args['label'].'_'.$args['field'];

        $this->dropdown_option_setting_options = get_option($the_option); ?>
<select data_form_id=<?php echo $args['label'] ?>
    name=<?php echo $the_option.'[dropdown_option_nutshell]'; ?>
    id="dropdown_option_nutshell">

    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'name') ? 'selected' : ''; ?>
    <option value="name" <?php echo $selected; ?>>Name</option>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'email') ? 'selected' : ''; ?>
    <option value="email" <?php echo $selected; ?>>Email</option>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'address') ? 'selected' : ''; ?>
    <option value="address" <?php echo $selected; ?>>Address
    </option>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'phone') ? 'selected' : ''; ?>
    <option value="phone" <?php echo $selected; ?>>Phone</option>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'notes') ? 'selected' : ''; ?>
    <option value="notes" <?php echo $selected; ?>>Notes</option>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'title') ? 'selected' : ''; ?>
    <option value="title" <?php echo $selected; ?>>Title</option>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell'] === 'description') ? 'selected' : ''; ?>
    <option value="description" <?php echo $selected; ?>>Description
    </option>
    <?php
        if (!$this->customFields) {
            return;
        }
        foreach ($this->customFields as $k=>$v) {
            if (is_array($v)) {
                foreach ($v as $vv) {
                    ?>
    <?php $selected = (isset($this->dropdown_option_setting_options['dropdown_option_nutshell']) && $this->dropdown_option_setting_options['dropdown_option_nutshell']  === str_replace(' ', '_', $vv->name)) ? 'selected' : ''; ?>
    <option value=<?php echo str_replace(' ', '_', $vv->name); ?>
        <?php echo $selected; ?>><?php echo $vv->name; ?>
    </option>
    <?php
                }
            }
        }
        echo '</select>';
    }

    public function dropdown_option_users_callback($args)
    {
        $the_option_users = 'dropdown_option_api_users';
        $the_option = 'dropdown_option_setting_api_users_'.$args['title'].'_api_users';

        $this->dropdown = get_option($the_option);

        if (!isset($this->dropdown['dropdown_option_api_users'])) {
            $this->dropdown['dropdown_option_api_users'] = get_option('nutshell_api_username');
        }

        $this->dropdown_option_api_users = array_values(get_option($the_option_users)); ?>
    <select name=<?php echo $the_option.'[dropdown_option_api_users]'; ?>
        id='dropdown_option_api_users'>
        <?php

        $total = count($this->dropdown_option_api_users);

        for ($i = 0; $i < $total; $i++) {
            ?>
        <?php $selected = (isset($this->dropdown['dropdown_option_api_users']) && $this->dropdown['dropdown_option_api_users'] == $this->dropdown_option_api_users[$i][1]) ? 'selected' : ''; ?>
        <option value=<?php echo $this->dropdown_option_api_users[$i][1]; ?>
            <?php echo $selected; ?>><?php echo $this->dropdown_option_api_users[$i][0]; ?>
        </option>
        <?php
        }
        echo '</select>';
    }

    public function dropdown_option_tags_callback($args)
    {
        $tags_num = 0;
        $this->dropdown_option_tags = get_option('wp_gf_nutshell_tags');
        $this->tags = get_transient('_s_nutshell_tags_results');

        if ($this->dropdown_option_tags) {
            $this->dropdown_option_tags = array_values($this->dropdown_option_tags);
            $tags_num = count($this->dropdown_option_tags);
        }

        echo  '<div id="output" data-id="'.$args['label'].'">';

        if (!empty($this->tags->Contacts)) {
            $output = '';
            $temp = '';

            $output .= '<select data-placeholder="Select one or more tags for this form" id="dropdown_option_api_tags_form_select" multiple class="chosen-select" style="min-width:30%;" >';
            foreach ($this->tags->Contacts as $tag) {
                $i = 0;
                $value = $temp = '';
                $value = str_replace(' ', '_', trim(strval($tag)));

                $keys = array_column($this->dropdown_option_tags, 'tag_text');

                if ($this->dropdown_option_tags) {
                    $all_selected = [];

                    while ($i < $tags_num) {
                        $key = null;
                        $id = $selected = '';

                        $id = $this->dropdown_option_tags[$i]['id'];
                        $saved_tag = $this->dropdown_option_tags[$i]['tag_text'];

                        if (($saved_tag == $value) && ($id == $args['label'])) {
                            $selected = 'selected';
                            array_push($all_selected, $value );
                            $temp = '<option value="'.$value.'"'. ' '.$selected.'>'.trim($tag).'</option>';
                        }
                        $i++;
                    }
                    $output.=$temp;
                    if (!in_array($value, $all_selected)) {
                        $output .= '<option value="'.$value.'"'. ' '.'>'.trim($tag).'</option>';
                    }
                }
            }
            $output .=$temp.= '</select>';
        } else {
            $output .= '<p>'.__('No tags found.', 'wp-gf-nutshell').'</p>';
        }
        echo $output; ?>
        </p>

        <?php
    }

    public function print_section_info()
    {
        return '';
    }

    public function print_user_info()
    {
        return '';
    }

    /*
     * Output text input
     */
    public function print_text_input($args, $type = 'value')
    {
        $placeholder = strcspn(strtolower($type), 'aeiou') > 0 ? "Please enter a $type" : "Please enter an $type";
        $value = !empty($args['value']) ? $args['value'] : '';

        printf(
            sprintf('<input type="text" id=%s name="%s" placeholder="%s" value="%s"></input>', $args['title'], $args['title'], $placeholder, $value)
        );
    }

    // set api users in transients; renew in a week
    public function setApiUsers()
    {
        if (false === ($api_users = get_transient('_s_nutshell_users_results'))) {
            $api_users = $this->getApiUsers();
            set_transient('_s_nutshell_users_results', $api_users, 7 * DAY_IN_SECONDS);
            update_option('dropdown_option_api_users', $api_users);
        }
    }

    // set api users in transients; renew in a week
    public function setApiTags()
    {
        if (false === ($api_tags = get_transient('_s_nutshell_tags_results'))) {
            $api_tags = $this->getApiTags();

            set_transient('_s_nutshell_tags_results', $api_tags, 2 * DAY_IN_SECONDS);
            update_option('dropdown_option_api_tags', $api_tags);
        }
    }

    public function getApiUsers()
    {
        global $gravity_forms;

        $api_users = [];
        $users = $gravity_forms->findApiUsers();

        foreach ($users as $user) {
            if ($user->isEnabled) {
                $api_users[] = [$user->name, $user->emails[0]];
            }
        }

        return $api_users;
    }

    public function getApiTags()
    {
        global $gravity_forms;

        $api_tags = [];

        $api_tags = $gravity_forms->findTags();

        return $api_tags;
    }

    public function cleanFormTitle($raw_title)
    {
        $form_title = preg_replace('/[^A-Za-z0-9 ]/', '', $raw_title);
        $form_title = str_replace(' ', '_', strtolower($form_title));

        return $form_title;
    }
}
