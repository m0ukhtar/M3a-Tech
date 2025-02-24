<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST["nom"]);
    $email = htmlspecialchars($_POST["email"]);
    $message = htmlspecialchars($_POST["message"]);

    $mail = new PHPMailer(true);

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Serveur SMTP (ex: Gmail)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'homem0ctar@gmail.com'; // Ton email
        $mail->Password   = 'Dormir444'; // Mot de passe ou App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Destinataire
        $mail->setFrom($email, $nom);
        $mail->addAddress('homem0ctar@gmail.com'); // Ton adresse de réception

        // Contenu de l'email
        $mail->isHTML(true);
        $mail->Subject = "Nouveau message de contact";
        $mail->Body    = "<h4>Nom:</h4> $nom<br><h4>Email:</h4> $email<br><h4>Message:</h4> <p>$message</p>";

        $mail->send();
        echo "Message envoyé avec succès !";
    } catch (Exception $e) {
        echo "Erreur : " . $mail->ErrorInfo;
    }
} else {
    echo "Méthode non autorisée.";
}
?>