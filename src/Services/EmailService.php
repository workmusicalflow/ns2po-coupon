<?php

namespace Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Utils\Logger;

class EmailService
{
    private PHPMailer $mailer;
    private Logger $logger;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->logger = new Logger();

        // Configure SMTP settings
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = $_ENV['SMTP_HOST'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $_ENV['SMTP_USERNAME'];
            $this->mailer->Password = $_ENV['SMTP_PASSWORD'];
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = $_ENV['SMTP_PORT'];
            $this->mailer->CharSet = 'UTF-8';

            // Set default sender
            $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        } catch (Exception $e) {
            $this->logger->error("Erreur de configuration email", ['error' => $e->getMessage()]);
            throw new \RuntimeException("Erreur de configuration email: " . $e->getMessage());
        }
    }

    public function sendActivationConfirmation(string $to, string $name, string $motif): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);

            $this->mailer->Subject = 'Confirmation d\'activation de votre coupon cadeau';

            // Create HTML message
            $this->mailer->Body = $this->getActivationEmailTemplate($name, $motif);

            // Create plain text version
            $this->mailer->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $this->mailer->Body));

            $this->mailer->send();
            $this->logger->info("Email de confirmation envoyé", ['to' => $to]);
            return true;
        } catch (Exception $e) {
            $this->logger->error("Erreur d'envoi d'email", [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private function getActivationEmailTemplate(string $name, string $motif): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Confirmation d'activation</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h1 style="color: #2563eb; margin-bottom: 20px;">Confirmation d'Activation</h1>
                
                <p>Bonjour {$name},</p>
                
                <p>Nous vous confirmons l'activation de votre coupon cadeau pour :</p>
                
                <div style="background-color: #f3f4f6; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 18px;">{$motif}</p>
                </div>
                
                <p>Nous traiterons votre commande dans les plus brefs délais.</p>
                
                <p>Cordialement,<br>
                L'équipe NS2PO</p>
            </div>
        </body>
        </html>
        HTML;
    }
}