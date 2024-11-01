<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.gulosolutions.com/
 * @since      1.0.0
 *
 * @package    Gravityforms_Nutshell_Integration
 * @subpackage Gravityforms_Nutshell_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gravityforms_Nutshell_Integration
 * @subpackage Gravityforms_Nutshell_Integration/admin
 * @author     Gulo <Gulo Solutions>
 */
class Gravityforms_Nutshell_Integration_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The tags option string for this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $tagsoption    The current saved tags.
     */

    const TAGS_SAVED_OPTIONS = 'wp_gf_nutshell_tags';

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */

    public function enqueue_admin_styles($hook)
    {
        if ('settings_page_gravityforms-nutshell-integration' !== $hook) {
            return;
        }

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . '/css/gravityforms-nutshell-integration-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_admin_scripts($hook)
    {
        if ('settings_page_gravityforms-nutshell-integration' !== $hook) {
            return;
        }

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '/js/gravityforms-nutshell-integration-admin.js', array('jquery'), $this->version, 'all');

        wp_localize_script(
            $this->plugin_name,
            'nutshell_tags',
            ['ajax_url' => admin_url('admin-ajax.php')]
        );
    }

    public function process_nutshell_tags()
    {
        $option = 'wp_gf_nutshell_tags';
        $tags = $_POST['ntags'];

        if (update_option($option, $tags)) {
            echo json_encode('1');
            wp_die();
        }

        wp_die();
    }

    public function add_error_message($err_msg='')
    {
        if (!empty($err_msg)) {
            ?>
<div class="error notice">
    <p><?php _e($err_msg.'!', 'wp-gf-nutshell'); ?>
    </p>
</div>
<?php
        }
    }

    public function _s_add_settings_link()
    {
        $file = $dir = '';
        $dir = dirname(__DIR__);
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if (strpos($fileInfo->getFilename(), 'nutshell') !== false) {
                $file = $fileInfo->getFilename();
            }
        }

        $page_link = pathinfo($file);
        $page_link = $page_link['filename'];

        $dir = explode(DIRECTORY_SEPARATOR, $dir);
        $dir = end($dir);
        $file = $dir.DIRECTORY_SEPARATOR.$file;

        add_filter('plugin_action_links_'.$file, function ($links) use ($page_link) {
            $links = array_merge(array(
                '<a href="' . esc_url(admin_url('options-general.php?page='.$page_link)) . '">' . __('Settings') . '</a>'
            ), $links);
            return $links;
        });
    }

    public function deactivate_plugin_if_gf_not_active()
    {
        if (!is_plugin_active('gravityforms/gravityforms.php')) {
            $error_message='WP GF Nutshell has been deactivated because a dependency -- Gravity Forms -- has been deactivated.';
            do_action('admin_notices', $error_message);
            deactivate_plugins(ABSPATH .'/wp-content/plugins/wp-gf-nutshell/gravityforms-nutshell-integration.php');
        }
    }
}
