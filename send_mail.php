<?php
/**
 * ADAMS PNEUMATIQUE SERVICES - Form Handler
 * Secure PHP Mailer for cPanel hosting
 */

header('Content-Type: application/json; charset=utf-8');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée.']);
    exit;
}

// Honeypot anti-spam check
if (!empty($_POST['website_hp'])) {
    // Silent fail for bots
    echo json_encode(['status' => 'success', 'message' => 'Message envoyé avec succès.']);
    exit;
}

// Sanitize inputs
$name = isset($_POST['name']) ? trim(filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '';
$phone = isset($_POST['phone']) ? trim(filter_var($_POST['phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '';
$service = isset($_POST['service']) ? trim(filter_var($_POST['service'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '';
$message = isset($_POST['message']) ? trim(filter_var($_POST['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS)) : '';

// Validation
if (empty($name) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Veuillez remplir votre nom et numéro de téléphone.']);
    exit;
}

// Header injection prevention (strip CRLF)
$name = str_replace(["\r", "\n"], '', $name);
$phone = str_replace(["\r", "\n"], '', $phone);

// Destination email (cPanel domain account)
$to = 'contact@adamspneumatique.ci';
$subject = "Nouvelle demande de devis site web - " . $name;

$body = "Demande de contact reçue depuis le site web adamspneumatique.ci :\n\n";
$body .= "Nom / Client : " . $name . "\n";
$body .= "Téléphone : " . $phone . "\n";
$body .= "Service demandé : " . ($service ?: 'Non spécifié') . "\n\n";
$body .= "Message / Détails :\n" . $message . "\n\n";
$body .= "--- Message envoyé le " . date('Y-m-d H:i:s') . " ---";

$headers = [];
$headers[] = 'From: Adams Web Site <noreply@adamspneumatique.ci>';
$headers[] = 'Reply-To: ' . $phone;
$headers[] = 'X-Mailer: PHP/' . phpversion();

// Send mail
$mailSent = @mail($to, $subject, $body, implode("\r\n", $headers));

if ($mailSent) {
    echo json_encode(['status' => 'success', 'message' => 'Votre demande a bien été envoyée ! Nos équipes vous contacteront rapidement.']);
} else {
    // Log error internally if needed, don't leak details
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossible d\'envoyer le message pour le moment. Veuillez nous contacter via WhatsApp.']);
}
