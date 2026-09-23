<?php
// ============================================================
//  csrf.php — Helper CSRF Token (inclure en haut de chaque page)
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();

// Générer le token s'il n'existe pas encore
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vérifier le token (appeler dans les POST)
function verifier_csrf() {
    $token_recu = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token_recu)) {
        http_response_code(403);
        die('Requête invalide — token CSRF manquant ou incorrect.');
    }
    // Régénérer après chaque utilisation
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Retourner le champ hidden HTML
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '"/>';
}
?>
