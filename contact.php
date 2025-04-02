<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';
require 'config.php';

session_start();

// Fonctions utilitaires
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function sendResponse($success, $message, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// Vérification de la méthode
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Méthode non autorisée", 405);
}

// Vérification CSRF
if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    sendResponse(false, "Token de sécurité invalide", 403);
}

// Vérification Honeypot
if (!empty($_POST['website'])) {
    sendResponse(false, "Requête bloquée", 403);
}

// Validation des données
$requiredFields = ['name', 'email', 'message', 'subject'];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        sendResponse(false, "Le champ $field est requis", 400);
    }
}

$name = cleanInput($_POST['name']);
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$subject = cleanInput($_POST['subject']);
$message = cleanInput($_POST['message']);

if (!$email) {
    sendResponse(false, "Adresse email invalide", 400);
}

if (strlen($message) > MAX_MESSAGE_LENGTH) {
    sendResponse(false, "Le message ne doit pas dépasser ".MAX_MESSAGE_LENGTH." caractères", 400);
}

// Construction du contenu de l'email
$emailContent = "
<!DOCTYPE html>
<html>
<head>
    <title>Nouveau message de contact</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { color: #00bcd4; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #eee; font-size: 0.8em; color: #777; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Nouveau message de contact</h2>
        </div>
        <p><strong>Nom:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Sujet:</strong> $subject</p>
        <h3>Message:</h3>
        <div style='background:#f5f5f5; padding:15px; border-radius:5px;'>".nl2br($message)."</div>
        <div class='footer'>
            Envoyé le ".date('d/m/Y à H:i')." depuis ".SITE_NAME."
        </div>
    </div>
</body>
</html>
";

$textContent = "Nouveau message de ".SITE_NAME."\n\n"
    ."Nom: $name\n"
    ."Email: $email\n"
    ."Sujet: $subject\n\n"
    ."Message:\n$message\n\n"
    ."Envoyé le ".date('d/m/Y à H:i');

// Envoi de l'email
try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($email, $name);
    $mail->addAddress(TO_EMAIL, TO_NAME);
    $mail->addReplyTo($email, $name);
    $mail->Subject = "[".SITE_NAME."] $subject";
    $mail->isHTML(true);
    $mail->Body = $emailContent;
    $mail->AltBody = $textContent;
    $mail->send();
    
    sendResponse(true, "Votre message a été envoyé avec succès. Nous vous contacterons bientôt!");

} catch (Exception $e) {
    error_log("Erreur d'envoi d'email: ".$e->getMessage());
    sendResponse(false, "Une erreur est survenue lors de l'envoi. Veuillez réessayer.", 500);
}
?>