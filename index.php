<?php

use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\HttpFoundation\Session\Session;

require "vendor/autoload.php";

/**
 * Session start
 */
/** @var Session $session */
$session = new Session();
var_dump($session->isStarted());
if(!$session->isStarted()){
    $session->start();
}

/**
 * Generate csrf security token for the form
 */
$csrfProvider = new CsrfTokenManager();
$csrf = $csrfProvider->getToken("main")->getValue();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="demo.php" method="POST">
        From:<br>
        <input type="text" name="email" id="email"><br>
        Subject:<br>
        <input type="text" name="subject" id="subject"><br>
        Message:<br>
        <textarea name="body" id="" cols="30" rows="10"></textarea><br>
        <input type="submit" name="submit" value="submit"><br>
        <input type="hidden" name="token" id="token" value="<?= $csrf ?>">
    </form>
</body>
</html>