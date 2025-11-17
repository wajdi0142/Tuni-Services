<?php
// api/forgot-password.php
use PHPMailer\PHPMailer\PHPMailer;
require '../vendor/autoload.php';
require '../db/config.php';

session_start();
header('Content-Type: application/json');

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = "forgot_attempts_$ip";
if (!isset($_SESSION[$key])) $_SESSION[$key] = ['count' => 0, 'time' => time()];
if ($_SESSION[$key]['count'] >= 5 && (time() - $_SESSION[$key]['time']) < 300) {
    echo json_encode(['success' => false, 'message' => 'Trop de tentatives. Réessayez dans 5 minutes.']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    $_SESSION[$key]['count']++;
    $_SESSION[$key]['time'] = time();
    echo json_encode(['success' => false, 'message' => 'Email invalide']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $message = "Si votre email est enregistré, un lien de réinitialisation vous a été envoyé.";

    if ($user) {
        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);

        $token = bin2hex(random_bytes(50));
        $expires = date("Y-m-d H:i:s", strtotime('+30 minutes'));
        $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$user['id'], $token, $expires]);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 0) == 443 ? "https" : "http";
        $resetLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/tuni-services/provider/reset-password.php?token=" . $token;

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'servicestuni@gmail.com';           // À CHANGER
        $mail->Password   = 'ebgbazocyjimterc';         // À CHANGER
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // LES 2 LIGNES MAGIQUES QUI RÉGLENT TOUT
        $mail->Encoding = 'base64';
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('no-reply@tuniservices.tn', 'Tuni-Services');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de mot de passe';

        $mail->Body = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:20px;background:#f9f9f9;border-radius:12px;">
            <h2 style="color:#007bff;text-align:center;">Tuni-Services</h2>
            <p>Bonjour <strong>' . htmlspecialchars($user['name']) . '</strong>,</p>
            <p style="text-align:center;margin:40px 0;">
                <a href="' . $resetLink . '" style="background:#007bff;color:white;padding:16px 40px;text-decoration:none;border-radius:50px;font-weight:bold;font-size:17px;">
                    Réinitialiser mon mot de passe
                </a>
            </p>
            <p><small style="word-break:break-all;">Ou copiez ce lien :<br>' . $resetLink . '</small></p>
            <p><small style="color:#888;">Valable 30 minutes</small></p>
        </div>';

        $mail->AltBody = "Bonjour {$user['name']},\n\n$resetLink\n\nValable 30 minutes.";

        $mail->send();
        $message = "Lien envoyé ! Vérifiez vos spams.";
    }

    unset($_SESSION[$key]);
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    // DEBUG COMPLET : on voit exactement ce que dit Gmail
    echo json_encode([
        'success' => false,
        'message' => 'ERREUR SMTP : ' . $e->getMessage()
    ]);
    exit;
}
?>