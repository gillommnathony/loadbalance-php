<?php
if (!defined('BASE_DIR')) exit();

$recaptcha_site_key = get_option('recaptcha_site_key');
if ($recaptcha_site_key) :
?>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback" async defer></script>
    <script>
        var $token = $('#captcha-response');
        var onloadCallback = function() {
            if ($token.length) {
                grecaptcha.ready(function() {
                    grecaptcha.execute();
                });
            }
        };

        function gCallback(token) {
            if (token !== '' && $token.length) $token.val(token);
            else swal('Error!', 'Failed to load captcha!', 'error');
        }

        $('#frm').on('submit', function() {
            if ($token.val() === '') {
                swal({
                        title: 'Error!',
                        text: 'Invalid Captcha! Load a new captcha.',
                        type: 'warning',
                        showCancelButton: true,
                        showLoaderOnConfirm: true,
                        cancelButtonClass: "btn-secondary",
                        confirmButtonClass: "btn-primary",
                        closeOnConfirm: false
                    },
                    function(isConfirm) {
                        if (!isConfirm) {
                            $('button#submit').prop('disabled', false);
                        } else {
                            grecaptcha.reset();
                            grecaptcha.execute().then(function() {
                                setTimeout(function() {
                                    $('button#submit').prop('disabled', false);
                                    swal('Success!', 'Captcha loaded successfully! Please resubmit the form.', 'success');
                                }, 5000);
                            });
                        }
                    });
                return false;
            }
            return true;
        });
    </script>
<?php endif; ?>
