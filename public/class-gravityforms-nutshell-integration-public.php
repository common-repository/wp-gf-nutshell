<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @see       https://www.gulosolutions.com/
 * @since      1.0.0
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @author     Gulo <Gulo Solutions>
 */
class Gravityforms_Nutshell_Integration_Public
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the ID of this plugin
     */
    private $plugin_name;

    /**
     * The Nutshell API var.
     *
     * @since    1.0.0
     *
     * @var string the ID of this plugin
     */
    private $nutshell;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     *
     * @var string the current version of this plugin
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     *
     * @param string $plugin_name the name of the plugin
     * @param string $version     the version of this plugin
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function startService()
    {
        global $gravity_forms;
        $gravity_forms =  Controllers\GravityFormsController::getInstance();
    }

    public function after_submission()
    {
        add_action('gform_after_submission', 'send_data_to_nutshell', 10, 2);

        function send_data_to_nutshell($entry, $form)
        {
            if (empty($form)) {
                exit;
            }

            global $gravity_forms;
            $notes = 'notes';
            $newContactId = $form_title = $form_owner = $user_id = $source_url = $phoneKey = '';
            $fields_to_update = $idLabelMap = $mapped = $data_to_send = $new_contact = $editContact = $users = $all_options = $form_options = $dataToSend = [];

            $form_title = preg_replace('/[^A-Za-z0-9 ]/', '', $form['title']);
            $form_title = str_replace(' ', '_', strtolower($form_title));

            foreach ($form['fields'] as $field) {
                if (!empty($field->label)) {
                    $option_name = str_replace(' ', '_', $field->label);
                    $option_name = strtolower($option_name);
                    $form_option = 'dropdown_option_setting_option_name_'.$form_title.'_'.$option_name.'_'.$form_title;
                }
                $mapped[$field->id] = get_option($form_option)['dropdown_option_nutshell'];
            }

            // get mapped values and assign them to keys
            foreach ($mapped as $key=>$value) {
                $dataToSend[$value] = $value;
            }

            // get form owner for admin
            $form_owner = get_option('dropdown_option_setting_api_users_'.$form_title.'_api_users')['dropdown_option_api_users'];

            // get tags for admin
            $the_option = 'dropdown_option_setting_tag_name_'.$form_title.'_api_tags';

            // get tag values from settings
            $tags_array = get_option($the_option);
            $restore_tags = function ($value) {
                return str_replace('_', ' ', $value);
            };

            if (!empty($tags_array)) {
                $tags_array = array_map($restore_tags, $tags_array);
            }

            foreach ($entry as $k => $v) {
                if (array_key_exists($k, $mapped)) {
                    $dataToSend[$mapped[$k]] = $v;
                }
            }

            if (!empty($tags_array)) {
                $dataToSend['tags'] = array_values($tags_array)[0];
            }

            $users = $gravity_forms->findApiUsers($form_owner);

            foreach ($users as $user) {
                if (in_array($form_owner, $user->emails)) {
                    $user_id = $user->id;
                    break;
                }
            }

            foreach ($entry as $k => $v) {
                if ($k == 'source_url') {
                    $source_url = $v;
                }
            }

            $custom_fields = $gravity_forms->findCustomFields();
            $custom_fields = (array) $custom_fields;
            $custom_fields_object = new stdClass();

            if (empty($dataToSend[$notes])) {
                $dataToSend[$notes] = '';
            }

            $dataToSend[$notes] = $dataToSend[$notes]."\r\n Source URL: ".$source_url;


            // foreach ($custom_fields as $field) {s
            //     if (is_array($field)) {
            //         foreach ($field as $ff) {
            //             if (in_array($ff->name, $custom_fields->Contacts)) {
            //                 $custom_fields_object[$ff->name] = $source_url;
            //                 $dataToSend['customFields'] = $custom_fields_object;
            //                 break;
            //             } else {
            //                 $dataToSend[$notes] = $dataToSend[$notes]."\r\n Source URL: ".$source_url;
            //                 break;
            //             }
            //         }
            //     }
            // }

            // search methods return stubs; get methods full info
            if (isset($dataToSend['email'])) {
                $contact = $gravity_forms->searchByEmail($dataToSend['email']);
            }

            if (!empty($contact->contacts[0]->id)) {
                $editContact = $gravity_forms->getContact($contact->contacts[0]->id);

                $emailKey = array_search($dataToSend['email'], (array) $editContact->email);
                if (isset($dataToSend['phone'])) {
                    $phoneKey = array_search($dataToSend['phone'], (array) $editContact->phone);
                }

                $editContact->phone = (array) $editContact->phone;
                $editContact->email = (array) $editContact->email;
                $editContact->rev = (array) $editContact->rev;
                $editContact->notes = (array) $editContact->notes;

                if (property_exists($editContact, 'tags')) {
                    $editContact->tags = (array) $editContact->tags;
                }

                if (!$emailKey) {
                    $fields_to_update['email'] = (isset($dataToSend['email']) && !is_null($dataToSend['email']) ? $dataToSend['email'] : ' ');
                } else {
                    $fields_to_update['email'][] = (array)$editContact->phone[$phoneKey];
                    $fields_to_update['email'][] = $dataToSend['email'];
                }

                if (!$phoneKey) {
                    $fields_to_update['phone'] = (isset($dataToSend['phone']) && !is_null($dataToSend['phone']) ? $dataToSend['phone'] : '111-111-1111');
                } else {
                    $fields_to_update['phone'][] = (array)$editContact->phone[$phoneKey];
                    $fields_to_update['phone'][] = $dataToSend['phone'];
                }

                $fields_to_update['owner'] = ['entityType' => 'Users', 'id' => $user_id];

                if (isset($dataToSend['organization'])) {
                    $fields_to_update['description'] = $dataToSend['organization'];
                }

                if (isset($dataToSend['tags'])) {
                    $tags = array_merge($editContact->tags, $dataToSend['tags']);
                    $fields_to_update['tags'] = $dataToSend['tags'];
                }

                $gravity_forms->editContact($editContact, $fields_to_update);

                if (!empty($dataToSend[$notes])) {
                    $gravity_forms->addNote(['entity' => ['entityType' => 'Contacts', 'id' => $editContact->id]], $dataToSend[$notes]);
                }
            } else {
                $new_contact['name'] = isset($dataToSend['name']) ? $dataToSend['name'] : ' ' ;
                $new_contact['email'] = isset($dataToSend['email']) ? $dataToSend['email'] : ' ' ;
                $new_contact['phone'] = isset($dataToSend['phone']) ? $dataToSend['phone'] : '111-111-1111' ;
                $new_contact['owner'] = ['entityType' => 'Users', 'id' => $user_id];

                if (!empty($dataToSend['organization'])) {
                    $new_contact['description'] = $dataToSend['organization'];
                }

                if (!empty($dataToSend['tags'])) {
                    $new_contact['tags'] = $dataToSend['tags'];
                }

                $params['contact'] = $new_contact;

                try {
                    if ($newContactId = $gravity_forms->addContact($params)) {
                        if (!empty($dataToSend[$notes])) {
                            $gravity_forms->addNote(['entity' => ['entityType' => 'Contacts', 'id' => $newContactId, 'name' => $dataToSend['name']]], $dataToSend[$notes]);
                        }
                    }
                } catch (Exception $e) {
                    error_log(print_r($e, true));
                }
            }
        }
    }
}
