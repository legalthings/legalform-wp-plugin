<?php

/*
 *   Plugin Name: Legalforms
 *   Description: This plugin can automatically create a legalform in a page by some shortcode
 *   Version: 1.0
 *   Author: Andrii Cherytsya & Jurre Wolsink (jurre@legalthings.io)
 */

define('LF', 'legalforms');
define('LF_PATH', plugin_dir_path( __FILE__ ));
load_plugin_textdomain(LF, false, basename(dirname(__FILE__)) . '/langs');

if (!defined('LF_LOG')) {
    define('LF_LOG', WP_CONTENT_DIR . '/cache/logs/');
}

class LegalForms
{
    public $config   = [];
    public $defaults = [];

    public function __construct()
    {
        $this->config = array_merge($this->defaults, get_option(LF));
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'initTinyMCEButton'));
        add_shortcode(LF, array($this, 'doLegalformShortcode'));
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
        // add_action("admin_print_scripts", [$this, 'addAdminScriptsCss']);

    }

    // public function addAdminScriptsCss()
    // {
    //     wp_enqueue_script('jquery-ui', "//code.jquery.com/ui/1.11.2/jquery-ui.min.js");
    // }

    /**
     * Load configuration page in admin area
     *
     * @return [strin] output of config page
     */
    public function configPage()
    {

        if (isset($_POST) && !empty($_POST)) {
            foreach ($_POST as $key => $value) {
                switch ($key) {
                    case 'base_url':
                        $this->config[$key] = (string) $value;
                        break;
                    default:
                        $this->config[$key] = rtrim(trim((string) $value), '/');
                        break;
                }
            }

            update_option(LF, $this->config);
        }

        include 'legalforms-options-page.php';
    }

    /**
     * Main function to change shortcode to create legalform
     *
     * @param  [array] Input attributes for form
     * @param  [string] inside text of shortcut
     * @return [string] final output of form shortcode
     */
    public function doLegalformShortcode($attrs, $content = null)
    {
        $attrs = shortcode_atts(array(
            'template' => '',
            'flow' => '',
            'material' => true,
        ), $attrs);
        $url = trim($this->config['base_url'], '/') . '/service/docx/templates/' . $attrs['template'] . '/forms';

        $s = curl_init();

        curl_setopt($s, CURLOPT_URL, $url);
        curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

        $form = curl_exec($s);

        $error = curl_errno($s);

        $info = curl_getinfo($s);
        if ($error || $info['content_type'] !== 'application/json' || $info['http_code'] !== 200) {
            return sprintf(__('Can not load form with reference: <a href="%s">%s</a>', LF), $url, $attrs['reference']);
        }

        $form = json_decode($form);
        $this->appendAssets($attrs, $form);
        ob_start();
        include LF_PATH.'legalforms-shortcode-html.php';
        $output = ob_get_clean();
        return $output;
    }

    /**
     *  Function to append legalform-js assets to the page with form
     *
     * @param  [object]
     */
    public function appendAssets($attrs, $form)
    {
        // Add bootstrap to the page
        if( (!wp_style_is('bootstrap', 'queue')) && (!wp_style_is('bootstrap', 'done'))) {
            wp_register_style('bootstrap', plugins_url('/vendor/bootstrap/bootstrap.css', __FILE__));
            wp_enqueue_style('bootstrap');
            wp_register_script('bootstrap', plugins_url('/vendor/bootstrap/bootstrap.js', __FILE__));
            wp_enqueue_script('bootstrap');
        }

        // Add selectize
        wp_register_script('selectize', plugins_url('/vendor/selectize/selectize.js', __FILE__));
        wp_enqueue_script('selectize');

        // Added material design if need
        if ($attrs['material'] !== 'false') {
            wp_register_style('bootstrap-material-design', plugins_url('/vendor/bootstrap-material-design/bootstrap-material-design.css', __FILE__));
            wp_enqueue_style('bootstrap-material-design');
            wp_register_script('bootstrap-material-design', plugins_url('/vendor/bootstrap-material-design/bootstrap-material-design.js', __FILE__));
            wp_enqueue_script('bootstrap-material-design');
        } else {
            wp_register_style('selectize', plugins_url('/vendor/selectize/selectize.css', __FILE__));
            wp_enqueue_style('selectize');
            wp_register_style('selectize-bootstrap', plugins_url('/vendor/selectize/selectize-bootstrap.css', __FILE__));
            wp_enqueue_style('selectize-bootstrap');
        }

        // Added font awensome fonts for labels
        wp_register_style('font-awesome', plugins_url('/vendor/bootstrap/font-awesome.css', __FILE__));
        wp_enqueue_style('font-awesome');

        // Add moment-js to the form
        wp_register_script('moment', plugins_url('/vendor/moment//moment.js', __FILE__));
        wp_enqueue_script('moment');
        wp_register_script('moment-nl', plugins_url('/vendor/moment/locale/nl.js', __FILE__));
        wp_enqueue_script('moment-nl');
        wp_register_script('moment-en', plugins_url('/vendor/moment/locale/en-gb.js', __FILE__));
        wp_enqueue_script('moment-en');

        // Add Ractive for form
        wp_register_script('ractive', plugins_url('/vendor/ractive/ractive.js', __FILE__));
        wp_enqueue_script('ractive');

        // Add perfect Scrollbar to form
        wp_register_style('perfect-scrollbar', plugins_url('/vendor/perfect-scrollbar/perfect-scrollbar.css', __FILE__));
        wp_enqueue_style('perfect-scrollbar');
        wp_register_script('perfect-scrollbar', plugins_url('/vendor/perfect-scrollbar/perfect-scrollbar.js', __FILE__));
        wp_enqueue_script('perfect-scrollbar');

        wp_register_script('validator', plugins_url('/vendor/validator/validator.js', __FILE__));
        wp_enqueue_script('validator');

        wp_register_script('jespath', plugins_url('/vendor/jmespath/jmespath.js', __FILE__));
        wp_enqueue_script('jespath');

        //Add Datetimepicker
        wp_register_style('datepicker', plugins_url('/vendor/datepicker/datepicker.css', __FILE__));
        wp_enqueue_style('datepicker');
        wp_register_script('datepicker', plugins_url('/vendor/datepicker/datepicker.js', __FILE__));
        wp_enqueue_script('datepicker');

        //Legalthings scripts and styles
        wp_register_style('legalform-wizard', plugins_url('/vendor/legalform-wizard/legalform-wizard.css', __FILE__));
        wp_enqueue_style('legalform-wizard');
        wp_register_script('legalform-wizard', plugins_url('/vendor/legalform-wizard/legalform-wizard.js', __FILE__));
        wp_enqueue_script('legalform-wizard');

        wp_register_style('legalform-css', plugins_url('/vendor/legalform/legalform.css', __FILE__));
        wp_enqueue_style('legalform-css');
        wp_register_script('legalform-js', plugins_url('/vendor/legalform/legalform.js', __FILE__), array('jquery', 'ractive'));
        wp_enqueue_script('legalform-js');

        wp_register_script(LF, plugins_url('/assets/js/Legalforms.js', __FILE__), array('jquery', 'ractive'), false, true);
        wp_register_style(LF, plugins_url('/assets/css/Legalforms.css', __FILE__));

        // Localize the script with new data
        $form_array = array(
            'id'                    => $form->id,
            'name'                  => $form->name,
            'definition'            => $form->definition,
            'material'              => $attrs['material'],
            'legalform_respond_url' => '10',
            'ajaxurl'               => admin_url( 'admin-ajax.php' ),
            'flow'                  => $attrs['flow'],
            'template'              => $attrs['template'],
            'base_url'              => $this->config['base_url']
        );

        wp_localize_script(LF, LF, $form_array);
        wp_enqueue_script(LF);
        wp_enqueue_style(LF);
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
        add_filter("mce_external_plugins", array($this, "registerMCEPlugin"));

        // Add a callback to add our button to the TinyMCE toolbar
        add_filter('mce_buttons', array($this, 'addButtonTinyMCE'));
    }

    public function registerMCEPlugin($plugin_array)
    {
        $plugin_array['legalforms_button'] = plugins_url('/assets/js/Legalforms-button.js', __FILE__);
        return $plugin_array;
    }

    public function addButtonTinyMCE($buttons)
    {
        $buttons[] = 'legalforms_button';
        return $buttons;
    }
}

global $legalforms;
$legalforms = new LegalForms();
register_activation_hook(__FILE__, array($legalforms, 'activate_plugin'));
register_deactivation_hook(__FILE__, array($legalforms, 'deactivate_plugin'));
