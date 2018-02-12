<?php

/*
 *   Plugin Name: LegalThings Legalforms
 *   Description: This plugin can automatically create a LegalThings LegalForm in a page by some shortcode
 *   Version: 1.2.2
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
            $options = get_option(LT_LFP);
            if (!empty($options)) {
                $this->config = array_merge($this->defaults, $options);
            } else {
                $this->config = $this->defaults;
            }

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
                'standard_login' => false,
                'done_url' => '',
                'alias_key' => '',
                'alias_value' => ''
            ), $attrs);

            $url = trim($this->config['base_url'], '/') . '/service/docx/templates/' . $attrs['template'] . '/forms';
            $response = wp_remote_get($url, array('timeout' => 10));

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
                wp_register_style('bootstrap', plugins_url('/vendor/bootstrap/legalforms-bootstrap.css', __FILE__));
                wp_enqueue_style('bootstrap');
                wp_register_script('bootstrap', plugins_url('/vendor/bootstrap/bootstrap.js', __FILE__));
                wp_enqueue_script('bootstrap');
            }

            // Add selectize
            wp_register_script('selectize', plugins_url('/vendor/selectize/selectize.js', __FILE__));
            wp_enqueue_script('selectize');

            // Added material design if need
            if ($attrs['material'] !== 'false') {
                wp_register_style('bootstrap-material-design', plugins_url('/vendor/bootstrap-material-design/legalforms-bootstrap-material-design.css', __FILE__));
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
            wp_register_style('legalform-wizard', plugins_url('/vendor/legalform-wizard/legalforms-legalform-wizard.css', __FILE__));
            wp_enqueue_style('legalform-wizard');
            wp_register_script('legalform-wizard', plugins_url('/vendor/legalform-wizard/legalform-wizard.js', __FILE__));
            wp_enqueue_script('legalform-wizard');

            wp_register_style('legalform-css', plugins_url('/vendor/legalform/legalforms-legalform.css', __FILE__));
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
                'legalform_respond_url' => '10',
                'ajaxurl'               => admin_url( 'admin-ajax.php' )
            );

            foreach ($this->config as $key => $value) {
                $form_array[sanitize_key($key)] = (string) sanitize_text_field($value);
            }
            foreach ($attrs as $key => $value) {
                switch ($key) {
                    case 'done_url':
                        $form_array[sanitize_key($key)] = (string) esc_url_raw($value);
                        break;
                    default:
                        $form_array[sanitize_key($key)] = (string) sanitize_text_field($value);
                        break;
                }
            }

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

        public function create_user($base_url, $account)
        {
            $response = wp_remote_post(
                $base_url . '/service/iam/users',
                array(
                    'timeout' => 15,
                    'body' => $account
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong: ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 201) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong: ' . $response['body'];
                die();
            }
        }

        public function create_session($base_url, $account)
        {
            $response = wp_remote_post(
                $base_url . '/service/iam/sessions',
                array(
                    'timeout' => 15,
                    'body' => $account
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong: ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 201) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong: ' . $response['body'];
                die();
            } else {
                return json_decode($response['body'], true);
            }
        }

        public function create_process($base_url, $session, $flow_data)
        {
            $response = wp_remote_post(
                $base_url . '/service/flow/processes',
                array(
                    'headers' => array(
                        'X-Session' => $session['id']
                    ),
                    'timeout' => 15,
                    'body' => $flow_data
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong: ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 201) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong: ' . $response['body'];
                die();
            } else {
                return json_decode($response['body'], true);
            }
        }

        public function process_legalform($data)
        {
            if ($data['register']) {
                $this->create_user($data['legalforms']['base_url'], $data['account']);
            }

            $session = $this->create_session($data['legalforms']['base_url'], $data['account']);

            $flow_data = array(
                'scenario' => $data['legalforms']['flow'],
                'data' => array(
                    'values' => $data['values'],
                    'template' => $data['legalforms']['template'],
                    'name' => $data['legalforms']['name'],
                    'organization' => $session['user']['employment'][0]['organization']['id']
                )
            );

            if ($data['legalforms']['alias_key'] && $data['legalforms']['alias_value']) {
                $flow_data['data']['alias'] = array(
                    'key' => $data['legalforms']['alias_key'],
                    'value' => $data['legalforms']['alias_value']
                );
            }

            $process = $this->create_process($data['legalforms']['base_url'], $session, $flow_data);

            if ($data['legalforms']['done_url'] != '') {
                echo $data['legalforms']['done_url'];
            } else {
                echo $data['legalforms']['base_url'] . '/processes/' . $process['id'] . '?auto_open=true&hash=' . $session['id'];
            }
        }
    }
}

global $legalforms;
$legalforms = new LegalThingsLegalForms();
register_activation_hook(__FILE__, array($legalforms, 'activate_plugin'));
register_deactivation_hook(__FILE__, array($legalforms, 'deactivate_plugin'));
