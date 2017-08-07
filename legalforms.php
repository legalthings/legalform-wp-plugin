<?php

/*
 *   Plugin Name: LegalThings Legalforms
 *   Description: This plugin can automatically create a LegalThings LegalForm in a page by some shortcode
 *   Version: 1.0.1
 *   Author: LegalThings
 */

define('LT_LFP', 'legalforms');
define('LT_LFP_PATH', plugin_dir_path( __FILE__ ));
load_plugin_textdomain(LT_LFP, false, basename(dirname(__FILE__)) . '/langs');

if (!defined('LT_LFP_LOG')) {
    define('LT_LFP_LOG', WP_CONTENT_DIR . '/cache/logs/');
}
if (!class_exists('LegalThingsLegalForms')) {
    class LegalThingsLegalForms
    {
        public $config   = [];
        public $defaults = [
          'base_url' => ''
        ];

        public function __construct()
        {
            $this->config = array_merge($this->defaults, get_option(LT_LFP));
            add_action('admin_menu', array($this, 'addAdminMenu'));
            add_action('admin_init', array($this, 'initTinyMCEButton'));
            add_shortcode(LT_LFP, array($this, 'doLegalformShortcode'));
        }

        public function activatePlugin()
        {
            //Add new options ids
            add_option(LT_LFP, $this->defaults, '', 'yes');
        }

        public function deactivatePlugin()
        {
            //remove mysql ids
            delete_option(LT_LFP);
        }

        /**
         * Functions in admin page
         */
        public function addAdminMenu()
        {
            add_options_page(__('Legalthings LegalForms configuration page', LT_LFP), __('LegalForms', LT_LFP), 'manage_options', LT_LFP, [$this, 'configPage']);
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
                            $this->config[sanitize_key($key)] = (string) esc_url_raw($value);
                            break;
                        default:
                            $this->config[sanitize_key($key)] = (string) sanitize_text_field($value);
                            break;
                    }
                }

                update_option(LT_LFP, $this->config);
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
            $response = wp_remote_get($url);

            if (wp_remote_retrieve_response_code($response) !== 200 ||
                wp_remote_retrieve_header($response, 'content-type') !== 'application/json') {
                return sprintf(__('Can not load form with reference: <a href="%s">%s</a>', LT_LFP), $url, $attrs['template']);
            }

            $form = json_decode(wp_remote_retrieve_body($response));
            $this->appendAssets($attrs, $form);
            ob_start();
            include LT_LFP_PATH.'legalforms-shortcode-html.php';
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

            wp_register_script(LT_LFP, plugins_url('/assets/js/Legalforms.js', __FILE__), array('jquery', 'ractive'), false, true);
            wp_register_style(LT_LFP, plugins_url('/assets/css/Legalforms.css', __FILE__));

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

            wp_localize_script(LT_LFP, LT_LFP, $form_array);
            wp_enqueue_script(LT_LFP);
            wp_enqueue_style(LT_LFP);
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
}

global $legalforms;
$legalforms = new LegalThingsLegalForms();
register_activation_hook(__FILE__, array($legalforms, 'activate_plugin'));
register_deactivation_hook(__FILE__, array($legalforms, 'deactivate_plugin'));
