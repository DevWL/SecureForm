<?php

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Validator\Validation;
use Devwl\Email\DataSanitizer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;

require "vendor/autoload.php";

// IDE VSCODE VS PHPSTORM -> https://www.youtube.com/watch?v=O9nIE-40uKk
// SYMFONY CONSTRAINTS -> https://symfony.com/doc/current/validation.html#string-constraints

/**
 * Get csrf form security token
 */
if($_POST){
    $csrfProvider = new CsrfTokenManager();
    $token = new CsrfToken("main", $_POST["token"]);
    // var_dump($csrfProvider->isTokenValid($token));
    if(!$csrfProvider->isTokenValid($token)){
        throw new \RuntimeException("Invalid token!");
    }
}

/**
 * Session var
 */
/** @var Session $session */
$session = new Session();

/**
 * Init Symfony Validator
 */
$validator = Validation::createValidator();
$error = [];

/**
 * Get current time
 */
$time = new \DateTime("now", new \DateTimeZone("Europe/Warsaw"));

// TODO - add form data
$email = DataSanitizer::userEmail($_POST['email']);

/** @var ConstraintViolationList $list */
$list = $validator->validate($email, 
    [
        new \Symfony\Component\Validator\Constraints\NotBlank(),
        new \Symfony\Component\Validator\Constraints\Length([
            'min' => 5,
            'max' => 100,
            'minMessage' => 'Your email must be at least {{ 5 }} characters long',
            'maxMessage' => 'Your email cannot be longer than {{ 100 }} characters',
        ]),
        new \Symfony\Component\Validator\Constraints\Email([
            "message" => "The email {{ {$email} }} is not a valid email.",
        ])
    ]);
if($list->count()){
    foreach ($list as $key => $cViolation) {
        /** @var ConstraintViolation $cViolation */
        $session->getFlashBag()->add("message", "{$cViolation->getMessage()}");
    }
}
$error[] = $list->count();


$subject = DataSanitizer::userString($_POST['subject']);
/** @var ConstraintViolationList $list */
$list = $validator->validate($subject, 
    [
        new \Symfony\Component\Validator\Constraints\NotBlank(),
        new \Symfony\Component\Validator\Constraints\Length([
            'min' => 2,
            'max' => 50,
            'minMessage' => 'Message subject must be at least {{ 2 }} characters long',
            'maxMessage' => 'Message subject cannot be longer than {{ 50 }} characters',
        ])
    ]);
if($list->count()){
    foreach ($list as $key => $cViolation) {
        /** @var ConstraintViolation $cViolation */
        $session->getFlashBag()->add("message", "{$cViolation->getMessage()}");
    }
}
$error[] = $list->count();

$body = DataSanitizer::userHTML($_POST['body']);
/** @var ConstraintViolationList $list */
$list = $validator->validate($body, 
    [
        new \Symfony\Component\Validator\Constraints\NotBlank(),
        new \Symfony\Component\Validator\Constraints\Length([
            'min' => 6,
            'max' => 1500,
            'minMessage' => 'Message body must be at least {{ 6 }} characters long',
            'maxMessage' => 'Message body cannot be longer than {{ 1500 }} characters',
        ])
    ]);
if($list->count()){
    foreach ($list as $key => $cViolation) {
        /** @var ConstraintViolation $cViolation */
        $session->getFlashBag()->add("message", "{$cViolation->getMessage()}");
    }
}
$error[] = $list->count();

echo "$email, $subject, $body, ";

/**
 * Read email and pass from json file (not commited to git repo)
 * create own json file config.json or raname demo tempconfig.json with "email" and "pass" keys with values
 */
$string = file_get_contents("config.json");
if ($string === false) {
    throw new Error("Missing config file. Create {{ ".__DIR__."".DIRECTORY_SEPARATOR."config.json }} from {{ ".__DIR__."".DIRECTORY_SEPARATOR."tempconfig.json }} file in " . __DIR__, 1);
    die();
}

$json = json_decode($string);
if ($json === null) {
    throw new Error("config.json can not be {{ null }}. Check json " . __DIR__ . "/config.json", 1);
    die();
}

// Send form
if($_POST["submit"] && array_sum($error) == 0){
    $transport = new Swift_SmtpTransport($json->smtp, 465, "ssl");
    $transport->setUsername($json->email);
    $transport->setPassword($json->pass);
    $mailer = new Swift_Mailer($transport);

    $message = new Swift_Message();
    $message
        ->addFrom($email)
        ->addTo($transport->getUsername())
        ->addReplyTo($email)
        ->setCharset("utf8")
        ->setSubject($subject)
        ->setContentType("text/html")
        ->setBody($body);

    $success = $mailer->send($message);

    /**
     * Display message about delivary status
     */
    if($success){
        $session->getFlashBag()->add("message","Message sent and recived! {$time->format('H-i-s')}");
    }else{
        $session->getFlashBag()->add("message","Something went wrong! Message not sent. Try again. {$time->format('H-i-s')}");
    }
}


/**
 * Print messages from falshbag
 */
foreach ($session->getFlashBag()->get("message") as $key => $m) {
    echo "<p>{$m}</p>";
}

/**
 * Debug - Display num of errors in each ConstraintViolationList
 */
print_r($error);



// header("Location: ./index.html");
die();
