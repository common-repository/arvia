<?php

/**
 * Plugin Name: Arvia
 * Author: Arvia
 * Author URI: www.arvia.tech
 * Plugin URI: https://arvia.tech/
 * Description: Omnichannel live video chat plugin, optimized for Wordpress. Fast, reliable and easy to use. Use the power of face to face interaction for remote sales and support
 * Version: 1.0.0.0
 *
 * Text Domain: arviachat
 */
$arviaSiteUrl = get_site_url();

$arvia_addr = 'https://arvia.tech';
$arviaUrl = 'https://arvia.chat';
$error = '';
$loginError = false;
$login = true;

define("ARVIA_PLUGIN_URL", plugin_dir_url(__FILE__));
define("ARVIA_IMG_URL", plugin_dir_url(__FILE__) . "/img/");
define("ARVIA_PLUGIN_VERSION", "1.0.0.0");
define("ARVIA_INTEGRATION_URL", $arviaUrl);
define("ARVIA_SITE_URL", $arviaSiteUrl);
define("ARVIA_UNINSTALL", "/api/integrations/wordpress/uninstall");

function arvia_admin_menu()
{
    add_menu_page(__('Arvia', 'arvia'), __('Arvia', 'arvia'), 'manage_options', basename(__FILE__), 'arviaPreferences', ARVIA_IMG_URL . "icon.png");
}

add_action('admin_menu', 'arvia_admin_menu');

function arviaPreferences()
{
    arviaUpdate();
    $ret =  Arvia::getInstance()->renderPage();
    if (is_array($ret)) {
        wp_send_json($ret);
    } else {
        _e($ret);
    }
}

function arviaUpdate()
{
    if (ARVIA_PLUGIN_VERSION !== get_option('arviachat_plugin_version')) {
        update_option('arviachat_plugin_version', ARVIA_PLUGIN_VERSION);
    }
}

register_activation_hook(__FILE__, 'arvia_activate');
function arvia_activate()
{
}

register_deactivation_hook(__FILE__, 'arvia_deactivate');
function arvia_deactivate()
{
}

register_uninstall_hook(__FILE__, 'arvia_uninstall');
function arvia_uninstall()
{
    $token = get_option('arviachat_token');
    if (!empty($token)) {
        $data = array(
            "hash" => get_option('arviachat_hash'),
            "domain" => ARVIA_SITE_URL
        );
        $res = wp_remote_post(ARVIA_INTEGRATION_URL . ARVIA_UNINSTALL, array(
            'headers'     => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Barear ' . $token
            ),
            'body'        => json_encode($data),
            'method'      => 'POST',
            'data_format' => 'body',
        ));

        if (wp_remote_retrieve_response_code($res) == 200) {
            $options = array(
                'arviachat_plugin_version',
                'arviachat_hash',
                'arviachat_token',
                'arviachat_projectId'
            );
            foreach ($options as $option) {
                if (get_option($option)) delete_option($option);
            }
        }
    }
}

if (!is_admin() &&  $GLOBALS['pagenow'] !== 'wp-login.php' ) {
    $projectId = get_option('arviachat_projectId');
    if (!empty($projectId)) {
        wp_enqueue_script(
            'arviaVideoCallWidget',
            'https://whitelabel.arvia.chat/widget/js/arvia-video-call-widget-bundle.js?projectId=' . $projectId,
            null,
            ARVIA_PLUGIN_VERSION,
            true
        );
    }
}

class Arvia
{
    protected static $instance;

    private static $LOGIN = '/api/auth/login';
    private static $SIGNUP = '/api/users';
    private static $SETUP = '/api/integrations/wordpress/setup';
    private static $SAVE = '/api/integrations/wordpress/save';

    private function __construct()
    {
        $this->transportEnabled = $this->isTransportEnabled();
    }

    private $transportEnabled;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Arvia();
        }

        return self::$instance;
    }

    private function isTransportEnabled()
    {
        if (!extension_loaded('curl') && !ini_get('allow_url_fopen')) {
            return false;
        }

        return true;
    }

    private function updateArviaOption($optionName, $optionValue)
    {
        if (current_user_can('manage_options')) {
            update_option('arviachat_' . $optionName, $optionValue);
        }
    }

    public function processRequest()
    {
        if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['login']) && $_POST['login'] == 'login') {

            $query['body'] = array(
                'email' => sanitize_email($_POST['email']),
                'password' => sanitize_text_field($_POST['password']),
                'integration' => 'wordpress',
                'domain' => ARVIA_SITE_URL
            );

            $response = $this->sendPost(self::$LOGIN, $query);
            if ($response) {
                if (wp_remote_retrieve_response_code($response) == 200) {
                    $body = json_decode(wp_remote_retrieve_body($response), true);
                    $this->updateArviaOption('token', $body['token']);
                    return true;
                } else {
                    $message = wp_remote_retrieve_body($response);
                    return array(
                        'login' => 'login',
                        'error' => $message,
                        'loginError' => true
                    );
                }
            }
        } elseif (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['signup']) && $_POST['signup'] == 'signup') {

            $query['body'] = array(
                'email' => sanitize_email($_POST['email']),
                'password' => sanitize_text_field($_POST['password']),
                'name' => sanitize_text_field($_POST['name']),
                'integration' => 'wordpress',
                'domain' => ARVIA_SITE_URL
            );

            $response = $this->sendPost(self::$SIGNUP, $query);

            if ($response) {
                if (wp_remote_retrieve_response_code($response) == 200) {
                    $body = json_decode(wp_remote_retrieve_body($response), true);
                    $this->updateArviaOption('token', $body['token']);
                    return true;
                } else {
                    $message = wp_remote_retrieve_body($response);
                    return  array(
                        'signup' => 'signup',
                        'error' => $message,
                        'signupError' => true
                    );
                }
            }
        } elseif (!empty($_POST['projectId']) && !empty($_POST['integration']) && $_POST['integration'] == 'wordpress') {
            $projectId = sanitize_text_field($_POST['projectId']);
            $this->updateArviaOption('projectId', $projectId);
            $data = array(
                'hash' => get_option('arviachat_hash'),
                'projectId' => $projectId,
            );
            $res = $this->saveSettings($data);
            if (wp_remote_retrieve_response_code($res) == 200) {
                return array(
                    'integration' => 'wordpress',
                    'message' => 'Settings saved successfully',
                    'success' => true
                );
            } else {
                return array(
                    'integration' => 'wordpress',
                    'message' => 'Something went wrong',
                    'success' => false
                );
            }
        } elseif (!empty($_POST['reset']) && $_POST['reset'] == 'reset') {
            $this->updateArviaOption('token', '');
            $this->updateArviaOption('projectId', '');
        } else {
            $current_user = wp_get_current_user();
            $query['body'] = array(
                'email' => $current_user->user_email,
                'firstName' => $current_user->user_firstname,
                'lastName' => $current_user->user_lastname,
                'id' => $current_user->ID,
                'integration' => 'wordpress',
                'domain' => ARVIA_SITE_URL
            );
            $response = $this->getData(self::$SETUP, $query);

            if ($response) {
                if (wp_remote_retrieve_response_code($response) == 200) {
                    $body = json_decode(wp_remote_retrieve_body($response), true);
                    if (!empty($body['hash'])) {
                        $this->updateArviaOption('hash', $body['hash']);
                    }
                    if (!empty($body['exists'])) {
                        if (!empty($body['token'])) {
                            $this->updateArviaOption('token', $body['token']);
                        }
                        return array(
                            'login' => 'login'
                        );
                    }
                } else if (wp_remote_retrieve_response_code($response) == 400) {
                    $body = json_decode(wp_remote_retrieve_body($response), true);
                    return array('error' => $body['errors']);
                } else {
                    $message = wp_remote_retrieve_body($response);
                    return array(
                        'login' => 'login',
                        'error' => $message
                    );
                }
            }
        }
    }

    private function saveSettings($data)
    {
        $res = wp_remote_post(ARVIA_INTEGRATION_URL . $this::$SAVE, array(
            'headers'     => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'Authorization' => 'Barear ' . get_option('arviachat_token')
            ),
            'body'        => json_encode($data),
            'method'      => 'POST',
            'data_format' => 'body',
        ));
        return $res;
    }

    private function sendPost($route, $query)
    {
        if (extension_loaded('curl')) {
            $res = wp_remote_post(ARVIA_INTEGRATION_URL . $route, $query);
            return $res;
        }

        return null;
    }

    private function getData($route, $query)
    {
        if (extension_loaded('curl')) {
            return wp_remote_get(ARVIA_INTEGRATION_URL . $route, $query);
        }

        return null;
    }

    public function renderPage()
    {
        if ($this->transportEnabled) {
            try {
                $result = $this->processRequest();
                if (is_array($result)) {
                    if (isset($result['login'])) {
                        if (isset($result['loginError'])) {
                            $loginError = $result['error'];
                        }
                        $login = true;
                    }
                    if (isset($result['signup'])) {
                        if (isset($result['signupError'])) {
                            $signupError = $result['error'];
                        }
                        $login = false;
                    }
                    if (isset($result['error'])) {
                        $error = $result['error'];
                    }
                    if (isset($result['integration'])) {
                        return array(
                            'arviaIntegrationMessage' => $result['message'],
                            'success' => $result['success']
                        );
                    }
                }
                require_once "templates/page.php";
            } catch (\Exception $e) {
                require_once "templates/error.php";
            }
        } else {
            require_once "templates/error.php";
        }
    }
}
