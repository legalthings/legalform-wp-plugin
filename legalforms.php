<?php

/*
 *   Plugin Name: Legalform
 *   Description: This plugin can automatically create legalform in page by some shortcode
 *   Version: 0.1
 *   Author: Andrii Cherytsya (poratuk@gmail.com)
 */

define('LF', 'legalforms');
load_plugin_textdomain(LF, false, basename(dirname(__FILE__)) . '/langs');

if (!defined('LF_LOG')) {
    define('LF_LOG', WP_CONTENT_DIR . '/cache/logs/');
}

class LegalForms
{
    public $config   = [];
    public $defaults = [
        'legalforms'   => 'http://legalform.com/',
        'useJQuery'    => false,
        'useBootstrap' => true,
        'useSelectize' => true,
    ];

    public function __construct()
    {

        $this->config = array_merge($this->defaults, get_option(LF));
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'initTinyMCEButton']);
        add_shortcode(LF, [$this, 'doLegalformShortcode']);

        /* End init */
    }

    public function activatePlugin()
    {
        //Add new options ids
        add_option(LF, $this->defaults, '', 'yes');
    }

    public function deactivatePlugin()
    {
        //remove mysql ids
        delete_option(LF);
    }

    /**
     * Functions in admin page
     */
    public function addAdminMenu()
    {
        add_options_page(__('LegalForms configuration page', LF), __('LegalForms', LF), 'manage_options', LF, [$this, 'configPage']);
        add_action("admin_print_scripts", [$this, 'addAdminScriptsCss']);

    }

    public function addAdminScriptsCss()
    {
        wp_enqueue_script('jquery-ui', "//code.jquery.com/ui/1.10.4/jquery-ui.min.js");
        wp_enqueue_script(LF . "-js", plugins_url('/assets/js/Legalforms.js', __FILE__));
        wp_enqueue_style(LF . "-css", plugins_url('/assets/css/Legalforms.css', __FILE__));
    }

    public function configPage()
    {

        if (isset($_POST) && !empty($_POST)) {
            foreach ($_POST as $key => $value) {
                switch ($key) {
                    case 'legalforms':
                        $this->config[$key] = rtrim(trim((string)$value), '/');
                        break;
                    case 'useJQuery':
                    case 'useBootstrap':
                    case 'useSelectize':
                        $this->config[$key] = (string)$value;
                        break;
                }
            }

            update_option(LF, $this->config);
        }
        include dirname(__FILE__).'includes/legalforms-options-page.php';
    }

    /**
    * Main function to change shortcode to create legalform
    */

    public function doLegalformShortcode($attrs, $content = null)
    {
        $a = shortcode_atts([
            'reference'     => '',
            'response_url'  => '',
            'redirect_page' => '',
            'useMaterial'    => false,
        ], $attrs);
        $url = trim($this->config['legalforms'], '/') . '/forms/' . $a['reference'];

        $s = curl_init(); 

        curl_setopt($s,CURLOPT_URL, $url); 
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true); 

        $form = curl_exec($s); 

        $error = curl_errno($s); 

        $info = curl_getinfo($s);
        if($error || $info['content_type'] !== 'application/json' || $info['http_code'] !== 200) { 
            return sprintf(__('Can not load form with reference: <a href="%s">%s</a>', LF), $url, $a['reference']) ;
        }

        $form = json_decode($form);

        $this->appendAssets($form);
        ob_start();
        include dirname(__FILE__).'./includes/legalforms.php';
        $output = ob_get_clean();
        return $output;
    }

    public function appendAssets($form) {

        // Register the script
        wp_register_script( LF.$a['reference'], 'path/to/myscript.js' );

        // Localize the script with new data
        $translation_array = array(
            'some_string' => __( 'Some string to translate', 'plugin-domain' ),
            'a_value' => '10'
        );

        wp_localize_script( 'some_handle', 'object_name', $translation_array );

        if ($this->config['useJQuery']) {
            wp_enqueue_script('jquery', "//code.jquery.com/ui/1.10.4/jquery-ui.min.js");
        }
    }

    /**
     * Add shorcode button to the TinyMCE text editor
     */
    public function initTinyMCEButton()
    {
        //Abort early if the user will never see TinyMCE
        if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
            return;
        }

        //Add a callback to regiser our tinymce plugin
        add_filter("mce_external_plugins", [$this, "registerMCEPlugin"]);

        // Add a callback to add our button to the TinyMCE toolbar
        add_filter('mce_buttons', [$this, 'addButtonTinyMCE']);
    }

    public function registerMCEPlugin($plugin_array)
    {
        $plugin_array['legalforms_button'] = plugins_url('/assets/js/Legalforms-button.js', __FILE__);
        return $plugin_array;
    }

    public function addButtonTinyMCE($buttons) {
        $buttons[] = 'legalforms_button';
        return $buttons;
    }
}
global $legalforms;
$legalforms = new LegalForms();
register_activation_hook(__FILE__, [$LegalForms, 'activate_plugin']);
register_deactivation_hook(__FILE__, [$LegalForms, 'deactivate_plugin']);
