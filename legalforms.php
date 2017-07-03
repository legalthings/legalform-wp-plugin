<?php

/*
 *   Plugin Name: Legalform
 *   Description: This plugin can automatically create legalform in page by some shortcode
 *   Version: 0.1
 *   Author: Andrii Cherytsya (poratuk@gmail.com)
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
    public $defaults = [
        'useJQuery'    => false
    ];

    public function __construct()
    {

        $this->config = array_merge($this->defaults, get_option(LF));
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('admin_init', array($this, 'initTinyMCEButton'));
        add_shortcode(LF, array($this, 'doLegalformShortcode'));

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
    }
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
        wp_register_style('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css');
        wp_enqueue_style('bootstrap');
        wp_register_script('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js');
        wp_enqueue_script('bootstrap');

        // Add selectize
        wp_register_script('selectize', '//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/js/standalone/selectize.min.js');
        wp_enqueue_script('selectize');

        // Added material design if need
        if ($attrs['material'] !== 'false') {
            wp_register_style('bootstrap-material-design', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.10/css/bootstrap-material-design.min.css');
            wp_enqueue_style('bootstrap-material-design');
            wp_register_script('bootstrap-material-design', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-material-design/0.5.10/js/material.min.js');
            wp_enqueue_script('bootstrap-material-design');
        } else {
            wp_register_style('selectize', '//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/css/selectize.default.min.css');
            wp_enqueue_style('selectize');
            wp_register_style('selectize-bootstrap', '//cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.4/css/selectize.bootstrap3.min.css');
            wp_enqueue_style('selectize-bootstrap');
        }

        // Added font awensome fonts for labels
        wp_register_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        wp_enqueue_style('font-awesome');

        // Add moment-js to the form
        wp_register_script('moment', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/moment.js');
        wp_enqueue_script('moment');
        wp_register_script('moment-nl', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/locale/nl.js');
        wp_enqueue_script('moment-nl');
        wp_register_script('moment-en', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.13.0/locale/en-gb.js');
        wp_enqueue_script('moment-en');

        // Add Ractive for form
        wp_register_script('ractive', '//cdnjs.cloudflare.com/ajax/libs/ractive/0.9.0-build-117/ractive.min.js');
        wp_enqueue_script('ractive');

        // Add perfect Scrollbar to form
        wp_register_style('perfect-scrollbar', '//cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/0.6.12/css/perfect-scrollbar.min.css');
        wp_enqueue_style('perfect-scrollbar');
        wp_register_script('perfect-scrollbar', '//cdnjs.cloudflare.com/ajax/libs/jquery.perfect-scrollbar/0.6.12/js/perfect-scrollbar.jquery.js');
        wp_enqueue_script('perfect-scrollbar');

        wp_register_script('validator', '//cdnjs.cloudflare.com/ajax/libs/1000hz-bootstrap-validator/0.11.9/validator.min.js');
        wp_enqueue_script('validator');

        wp_register_script('jespath', '//cdn.rawgit.com/jmespath/jmespath.js/master/jmespath.js');
        wp_enqueue_script('jespath');

        //Add Datetimepicker
        wp_register_style('datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker-standalone.min.css');
        wp_enqueue_style('datepicker');
        wp_register_script('datepicker', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js');
        wp_enqueue_script('datepicker');

        //Legalthings scripts and styles
        wp_register_style('legalform-wizard', '//s3-eu-west-1.amazonaws.com/legalthings-cdn/bootstrap-wizard/bootstrap-wizard.css');
        wp_enqueue_style('legalform-wizard');
        wp_register_script('legalform-wizard', '//s3-eu-west-1.amazonaws.com/legalthings-cdn/bootstrap-wizard/bootstrap-wizard.js');
        wp_enqueue_script('legalform-wizard');

        wp_register_style('legalform-css', '//rawgit.com/legalthings/legalform-js/master/dist/legalform.css');
        wp_enqueue_style('legalform-css');
        wp_register_script('legalform-js', '//rawgit.com/legalthings/legalform-js/master/dist/legalform.js', array('jquery', 'ractive'));
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
