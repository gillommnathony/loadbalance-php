<?php
if (!defined('BASE_DIR')) exit();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation Email</title>
</head>

<body>
    <h1>Hi {recepient_name},</h1>
    <p style="font-size:1.4em;">Thank you for registering on {sitename}. Please confirm your email by clicking the following link <a href="{email_confirmation_link}" target="_blank">{email_confirmation_link}</a></p>
</body>

</html>
