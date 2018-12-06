<?php

/*
 *   Plugin Name: LegalThings Legalforms
 *   Description: This plugin can automatically create a LegalThings LegalForm in a page by some shortcode
 *   Version: 1.7
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
          'base_url' => '',
          'load_bootstrap' => true
        ];

        public function __construct()
        {
            $options = get_option(LT_LFP);
            if (!empty($options)) {
                $this->config = array_merge($this->defaults, $options);
            } else {
                $this->config = $this->defaults;
            }

            add_action('admin_menu', array($this, 'add_admin_menu'));
            add_action('admin_init', array($this, 'init_tinyMCE_button'));

            add_action('wp_ajax_process_legalform', array($this, 'process_legalform'));
            add_action('wp_ajax_nopriv_process_legalform', array($this, 'process_legalform'));

            add_action('wp_ajax_forgot_password', array($this, 'forgot_password'));
            add_action('wp_ajax_nopriv_forgot_password', array($this, 'forgot_password'));


            add_shortcode(LT_LFP, array($this, 'do_legalform_shortcode'));
        }

        public function activate_plugin()
        {
            //Add new options ids
            add_option(LT_LFP, $this->defaults, '', 'yes');
        }

        public function deactivate_plugin()
        {
            //remove mysql ids
            delete_option(LT_LFP);
        }

        /**
         * Functions in admin page
         */
        public function add_admin_menu()
        {
            add_options_page(__('Legalthings LegalForms configuration page', LT_LFP), __('LegalForms', LT_LFP), 'manage_options', LT_LFP, [$this, 'config_page']);
        }

        /**
         * Load configuration page in admin area
         *
         * @return [strin] output of config page
         */
        public function config_page()
        {

            if (isset($_POST) && !empty($_POST)) {
                foreach ($_POST as $key => $value) {
                    switch ($key) {
                        case 'base_url':
                        case 'terms_url':
                            $this->config[sanitize_key($key)] = rtrim((string) esc_url_raw($value), '/');
                            break;
                        default:
                            $this->config[sanitize_key($key)] = (string) sanitize_text_field($value);
                            break;
                    }
                }

                if (isset($_POST['load_bootstrap'])) {
                    $this->config['load_bootstrap'] = true;
                } else {
                    $this->config['load_bootstrap'] = false;
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
        public function do_legalform_shortcode($attrs, $content = null)
        {
            $attrs = shortcode_atts(array(
                'template' => '',
                'flow' => '',
                'material' => true,
                'standard_login' => false,
                'done_url' => '',
                'alias_key' => '',
                'alias_value' => '',
                'step_through' => false,
                'ask_email' => false,
                'test_mode' => false
            ), $attrs);
                        
            if ($attrs['test_mode'] == 'true') {
                $url = plugins_url('/test_form.json', __FILE__);
                $form = json_decode(file_get_contents($url));
            } else {
                $url = $this->config['base_url'] . '/service/docx/templates/' . $attrs['template'] . '/forms';
                
                $response = wp_remote_get($url, array('timeout' => 10));
                
                if (wp_remote_retrieve_response_code($response) !== 200 ||
                wp_remote_retrieve_header($response, 'content-type') !== 'application/json') {
                    return sprintf(__('Can not load form with reference: <a href="%s">%s</a>', LT_LFP), $url, $attrs['template']);
                }
                
                $form = json_decode(wp_remote_retrieve_body($response));
            }
            $this->append_assets($attrs, $form);
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
        public function append_assets($attrs, $form)
        {
            // Add bootstrap to the page
            if( (!wp_style_is('bootstrap', 'queue')) && (!wp_style_is('bootstrap', 'done')) &&
                $this->config['load_bootstrap']) {
                wp_register_style('bootstrap', plugins_url('/vendor/bootstrap/legalforms-bootstrap.css', __FILE__));
                wp_enqueue_style('bootstrap');
                wp_register_script('bootstrap', plugins_url('/vendor/bootstrap/bootstrap.js', __FILE__));
                wp_enqueue_script('bootstrap');
            }

            // Add inputmask
            wp_register_script('inputmask', plugins_url('/vendor/inputmask/inputmask.js', __FILE__));
            wp_enqueue_script('inputmask');

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

            wp_register_script('jmespath', plugins_url('/vendor/jmespath/jmespath.js', __FILE__));
            wp_enqueue_script('jmespath');

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

            wp_register_script('es6-shim', plugins_url('/vendor/es6-shim/es6-shim.js', __FILE__));
            wp_enqueue_script('es6-shim');

            // Localize the script with new data
            $form_array = array(
                'id'                    => $form->id,
                'name'                  => $form->name,
                'definition'            => $form->definition,
                'legalform_respond_url' => '10',
                'ajaxurl'               => admin_url( 'admin-ajax.php' ),
                'dir_url'               => plugin_dir_url(__FILE__),
                'base_url'              => $this->config['base_url']
            );

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
        public function init_tinyMCE_button()
        {
            //Abort early if the user will never see TinyMCE
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages') && get_user_option('rich_editing') == 'true') {
                return;
            }

            //Add a callback to regiser our tinymce plugin
            add_filter("mce_external_plugins", array($this, "register_MCE_plugin"));

            // Add a callback to add our button to the TinyMCE toolbar
            add_filter('mce_buttons', array($this, 'add_button_tinyMCE'));
        }

        public function register_MCE_plugin($plugin_array)
        {
            $plugin_array['legalforms_button'] = plugins_url('/assets/js/Legalforms-button.js', __FILE__);
            return $plugin_array;
        }

        public function add_button_tinyMCE($buttons)
        {
            $buttons[] = 'legalforms_button';
            return $buttons;
        }

        public function create_user($base_url, $account)
        {
            $response = wp_remote_post(
                $base_url . '/service/iam/users',
                array(
                    'timeout' => 30,
                    'body' => $account
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong (create_user): ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 201) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong (create_user): ' . $response['body'];
                die();
            }
        }

        public function create_session($base_url, $payload)
        {    
            $response = wp_remote_post(
                $base_url . '/service/iam/sessions',
                array(
                    'timeout' => 30,
                    'body' => $payload,
                )
            );
            
            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong (create_session): ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 201) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong (create_session): ' . $response['body'];
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
                    'timeout' => 30,
                    'body' => $flow_data
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong (create_process): ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 201) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong (create_process): ' . $response['body'];
                die();
            } else {
                return json_decode($response['body'], true);
            }
        }

        public function step_through($base_url, $session, $process)
        {
            $response = wp_remote_post(
                $base_url . '/service/flow/processes/'. $process['id'] . '/response',
                array(
                    'headers' => array(
                        'X-Session' => $session['id']
                    ),
                    'timeout' => 30,
                    'body' => array(
                        'response' => 'ok',
                        'action' => $process['current']['key']
                    )
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong (step_through): ' . $error_message;
                die();
            } else if ($response['response']['code'] !== 200) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong (step_through): ' . $response['body'];
                die();
            } else {
                return json_decode($response['body'], true);
            }
        }

        public function process_legalform()
        {
            if ($_POST['register'] === 'true') {
                $this->create_user($this->config['base_url'], $_POST['account']);
            }

            if ($_POST['legalforms']['standard_login'] === 'true') {
                $payload = array(
                    'email' => strtolower($this->config['standard_email']),
                    'password' => $this->config['standard_password'],
                    'ttl' => 600
                );

                $session = $this->create_session($this->config['base_url'], $payload);
            } else {
                $session = $this->create_session($this->config['base_url'], $_POST['account']);
            }


            $flow_data = array(
                'scenario' => $_POST['legalforms']['flow'],
                'data' => array(
                    'values' => $_POST['values'],
                    'template' => $_POST['legalforms']['template'],
                    'name' => $_POST['legalforms']['name'],
                    'organization' => $session['user']['employment'][0]['organization']['id']
                )
            );

            if (isset($_POST['account']['user_email'])) {
                $flow_data['data']['user_email'] = strtolower($_POST['account']['user_email']);
                $flow_data['data']['user_name'] = $_POST['account']['user_name'];
            }

            if (($_POST['legalforms']['alias_key']) && $_POST['legalforms']['alias_value']) {
                $flow_data['data']['alias'] = array(
                    'key' => $_POST['legalforms']['alias_key'],
                    'value' => $_POST['legalforms']['alias_value']
                );
            }

            $process = $this->create_process($this->config['base_url'], $session, $flow_data);

            if ($process['current']['definition'] === 'legaldocx' && $_POST['legalforms']['step_through'] === 'true') {
                $return = $this->step_through($this->config['base_url'], $session, $process);
            }

            if ($_POST['legalforms']['done_url']) {
                echo $_POST['legalforms']['done_url'];
            } else if ($_POST['legalforms']['standard_login'] === 'true') {
                echo $this->config['base_url'] . '/processes/' . $process['id'];
            } else {
                echo $this->config['base_url'] . '/processes/' . $process['id'] . '?hash=' . $session['id'] . '&auto_open=true';
            }

            wp_die();
        }

        public function forgot_password() {
            $response = wp_remote_post(
                $this->config['base_url'] . '/service/iam/sessions',
                array(
                  'timeout' => 15,
                  'body' => array(
                    'email' => $_POST['email'],
                    'forgotpassword' => true
                  )
                )
            );

            if (is_wp_error($response)) {
                header('HTTP/1.1 500 Internal Server Error');
                $error_message = $response->get_error_message();
                echo 'Something went wrong: ' . $error_message;
                die();
            } else if ($response['response']['code'] === 400) {
                header('HTTP/1.1 ' . $response['response']['code'] . ' ' . $response['response']['message']);
                echo 'Something went wrong: ' . $response['body'];
                die();
            } else {
                echo $response['body'];
            }
        }
    }
}

global $legalforms;
$legalforms = new LegalThingsLegalForms();
register_activation_hook(__FILE__, array($legalforms, 'activate_plugin'));
register_deactivation_hook(__FILE__, array($legalforms, 'deactivate_plugin'));
