<?php
wp_enqueue_style('arvia_css', ARVIA_PLUGIN_URL . '/css/arvia.css');
wp_enqueue_style('bootstrap', ARVIA_PLUGIN_URL . '/css/bootstrap.min.css');
wp_enqueue_script('bootstrap_js',  ARVIA_PLUGIN_URL . '/js/bootstrap.min.js');
?>

<?php
$token = get_option('arviachat_token');
if ($token && !empty($token)) {
    $args = array(
        'headers' => array(
            'Authorization' => 'Barear ' . $token
        )
    );
    $res = wp_remote_get(ARVIA_INTEGRATION_URL . '/api/projects', $args);
    if (wp_remote_retrieve_response_code($res) == 200) {
        require 'integration.php';
    } else {
        $login  = true;
        require 'login.php';
    }
} else {
    if (!empty($error)) {
        require 'error.php';
    } else {
        require 'signup.php';
        require 'login.php';
    }
}
?>

<script type="text/javascript">
    (function(jQuery) {
        window.$ = jQuery.noConflict();
    })(jQuery);
    (function($) {
        $(function() {
            $('#signupForm').submit(function() {
                $('#signupButton').attr('disabled', true);
            });
            $('#loginForm').submit(function() {
                $('#loginButton').attr('disabled', true);
            });
            $('#resetForm').submit(function() {
                $('#resetButton').attr('disabled', true);
            });

            $('#loginLink').click(
                function() {
                    $('#loginBlock').css('display', 'block');
                    $('#signupBlock').css('display', 'none');
                }
            );
            $('#signupLink').click(
                function() {
                    $('#loginBlock').css('display', 'none');
                    $('#signupBlock').css('display', 'block');
                    $(window).scrollTop(0);
                }
            );

        });
    })(jQuery);
</script>