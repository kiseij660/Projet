<?php
// ============================================================
//  inscription.php — Reçoit le formulaire et sauvegarde en BDD
// ============================================================

$host = 'localhost';
$dbname = 'vieadeux';
$user = 'root';
$pass = '';          // XAMPP : mot de passe vide par défaut

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['succes' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]));
}

// Récupération et nettoyage des données
$prenom      = htmlspecialchars(trim($_POST['prenom'] ?? ''));
$nom         = htmlspecialchars(trim($_POST['nom'] ?? ''));
$je_suis     = htmlspecialchars(trim($_POST['je_suis'] ?? ''));
$je_cherche  = htmlspecialchars(trim($_POST['je_cherche'] ?? ''));
$ville       = htmlspecialchars(trim($_POST['ville'] ?? ''));
$age         = intval($_POST['age'] ?? 0);
$telephone   = htmlspecialchars(trim($_POST['telephone'] ?? ''));
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

// Validations de base
if (empty($prenom) || empty($nom) || empty($je_suis) || empty($ville) || empty($telephone) || empty($mot_de_passe)) {
    header('Location: index.html?erreur=champs_manquants');
    exit;
}
if ($age < 18 || $age > 120) {
    header('Location: index.html?erreur=age_invalide');
    exit;
}

// Hash du mot de passe (sécurité)
$hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

// Insertion en base
$sql = "INSERT INTO inscriptions (prenom, nom, je_suis, je_cherche, ville, age, telephone, mot_de_passe, date_inscription)
        VALUES (:prenom, :nom, :je_suis, :je_cherche, :ville, :age, :telephone, :mot_de_passe, NOW())";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':prenom'       => $prenom,
    ':nom'          => $nom,
    ':je_suis'      => $je_suis,
    ':je_cherche'   => $je_cherche,
    ':ville'        => $ville,
    ':age'          => $age,
    ':telephone'    => $telephone,
    ':mot_de_passe' => $hash,
]);

// Redirection vers page de confirmation
header("Location: connexion.php");
// Compte créé, aller se connecter
exit;
?>
