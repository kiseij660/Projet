<?php
// ============================================================
//  inscription.php v2 — Avec email, date naissance, validation
// ============================================================
session_start();

$host   = 'localhost';
$dbname = 'vieadeux';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur BDD : ' . $e->getMessage());
}

// ── Récupération ──
$prenom           = htmlspecialchars(trim($_POST['prenom'] ?? ''));
$nom              = htmlspecialchars(trim($_POST['nom'] ?? ''));
$email            = htmlspecialchars(trim($_POST['email'] ?? ''));
$je_suis          = htmlspecialchars(trim($_POST['je_suis'] ?? ''));
$je_cherche       = htmlspecialchars(trim($_POST['je_cherche'] ?? ''));
$ville            = htmlspecialchars(trim($_POST['ville'] ?? ''));
$date_naissance   = $_POST['date_naissance'] ?? '';
$telephone        = htmlspecialchars(trim($_POST['telephone'] ?? ''));
$centres          = htmlspecialchars(trim($_POST['centres_interet'] ?? ''));
$mot_de_passe     = $_POST['mot_de_passe'] ?? '';
$confirm_mdp      = $_POST['confirm_mdp'] ?? '';

// ── Normalisation du téléphone ──
function normaliserTelephone($tel) {
    // Supprimer tout sauf chiffres et +
    $tel = preg_replace('/[^0-9+]/', '', $tel);
    // Convertir 06... ou 07... en +336... ou +337...
    if (preg_match('/^0[67]/', $tel)) {
        $tel = '+33' . substr($tel, 1);
    }
    // Convertir 336... en +336...
    if (preg_match('/^33[67]/', $tel)) {
        $tel = '+' . $tel;
    }
    return $tel;
}
$telephone = normaliserTelephone($telephone);

// ── Validations ──
$erreurs = [];

if (empty($prenom) || empty($nom))         $erreurs[] = 'Prénom et nom requis.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                                           $erreurs[] = 'Email invalide.';
if (empty($je_suis))                       $erreurs[] = 'Veuillez indiquer votre genre.';
if (empty($ville))                         $erreurs[] = 'Ville requise.';
if (empty($telephone))                     $erreurs[] = 'Téléphone requis.';
if (!empty($telephone) && !preg_match('/^\+[1-9][0-9]{7,14}$/', $telephone))
                                           $erreurs[] = 'Numéro de téléphone invalide. Format : +33 6 XX XX XX XX';
if (empty($date_naissance))               $erreurs[] = 'Date de naissance requise.';

// Vérification âge minimum 18 ans
if (!empty($date_naissance)) {
    $age = (int)((new DateTime())->diff(new DateTime($date_naissance))->y);
    if ($age < 18) $erreurs[] = 'Vous devez avoir au moins 18 ans.';
}

// Validation mot de passe
if (strlen($mot_de_passe) < 8)            $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
if (!preg_match('/[A-Z]/', $mot_de_passe)) $erreurs[] = 'Le mot de passe doit contenir au moins une majuscule.';
if (!preg_match('/[0-9]/', $mot_de_passe)) $erreurs[] = 'Le mot de passe doit contenir au moins un chiffre.';
if ($mot_de_passe !== $confirm_mdp)        $erreurs[] = 'Les mots de passe ne correspondent pas.';

// Vérifier email unique
if (empty($erreurs)) {
    $check = $pdo->prepare("SELECT id FROM inscriptions WHERE email = :email LIMIT 1");
    $check->execute([':email' => $email]);
    if ($check->fetch()) $erreurs[] = 'Cet email est déjà utilisé.';
}

if (!empty($erreurs)) {
    // Afficher les erreurs directement pour debug
    echo '<div style="background:#fce8e8;padding:20px;font-family:sans-serif;border:2px solid red;margin:20px;">';
    echo '<h3>Erreurs détectées :</h3><ul>';
    foreach ($erreurs as $e) echo "<li>$e</li>";
    echo '</ul></div>';
    exit;
}

// ── Hash & insertion ──
$hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO inscriptions 
    (prenom, nom, email, je_suis, je_cherche, ville, date_naissance, telephone, centres_interet, mot_de_passe)
    VALUES (:prenom, :nom, :email, :je_suis, :je_cherche, :ville, :date_naissance, :telephone, :centres, :mdp)");

$stmt->execute([
    ':prenom'         => $prenom,
    ':nom'            => $nom,
    ':email'          => $email,
    ':je_suis'        => $je_suis,
    ':je_cherche'     => $je_cherche,
    ':ville'          => $ville,
    ':date_naissance' => $date_naissance,
    ':telephone'      => $telephone,
    ':centres'        => $centres,
    ':mdp'            => $hash,
]);

header('Location: connexion.php?inscrit=1');
exit;
?>
