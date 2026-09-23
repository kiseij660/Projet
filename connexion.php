<?php
// ============================================================
//  connexion.php v2 — Login par email OU téléphone
// ============================================================
session_start();
require_once 'csrf.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: profils.php');
    exit;
}

$host   = 'localhost';
$dbname = 'vieadeux';
$user   = 'root';
$pass   = '';

$erreur  = '';
$succes  = '';

// Message après inscription
if (isset($_GET['inscrit'])) {
    $succes = 'Inscription réussie ! Connectez-vous maintenant.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $identifiant  = htmlspecialchars(trim($_POST['identifiant'] ?? ''));
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Normaliser si c'est un téléphone
    if (preg_match('/^[0-9+]/', $identifiant)) {
        $identifiant = preg_replace('/[^0-9+]/', '', $identifiant);
        if (preg_match('/^0[67]/', $identifiant)) {
            $identifiant = '+33' . substr($identifiant, 1);
        }
    }

    if (empty($identifiant) || empty($mot_de_passe)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Login par email OU téléphone
            $stmt = $pdo->prepare("SELECT * FROM inscriptions 
                WHERE (email = :id OR telephone = :id) AND actif = 1 LIMIT 1");
            $stmt->execute([':id' => $identifiant]);
            $membre = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($membre && password_verify($mot_de_passe, $membre['mot_de_passe'])) {
                $_SESSION['user_id'] = $membre['id'];
                $_SESSION['prenom']  = $membre['prenom'];
                $_SESSION['nom']     = $membre['nom'];
                $_SESSION['role']    = 'membre';
                header('Location: profils.php');
                exit;
            } else {
                $erreur = 'Identifiants incorrects. Vérifiez votre email/téléphone et mot de passe.';
            }
        } catch (PDOException $e) {
            $erreur = 'Erreur de connexion à la base de données.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
    <meta name="robots" content="noindex, nofollow"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Connexion — Vie à deux</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif; min-height: 100vh;
      display: grid; grid-template-columns: 1fr 1fr; background: #FAF6F0;
    }
    .left {
      background:
        linear-gradient(135deg, rgba(139,26,26,0.88), rgba(58,34,24,0.75)),
        url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=900&q=80') center/cover;
      display: flex; flex-direction: column; justify-content: center;
      align-items: flex-start; padding: 60px; color: #fff;
    }
    .left-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 600; margin-bottom: 48px; text-decoration: none; color: #fff; }
    .left h1 { font-family: 'Cormorant Garamond', serif; font-size: 2.8rem; font-weight: 300; line-height: 1.2; margin-bottom: 20px; }
    .left h1 em { font-style: italic; color: #f0c0c0; }
    .left p { color: rgba(255,255,255,0.7); line-height: 1.7; max-width: 380px; }
    .left-badges { display: flex; flex-direction: column; gap: 14px; margin-top: 40px; }
    .left-badge { display: flex; align-items: center; gap: 12px; font-size: 0.88rem; color: rgba(255,255,255,0.8); }
    .left-badge .icon { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .right { display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 60px 40px; }
    .form-card { width: 100%; max-width: 400px; }
    .form-card h2 { font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 600; color: #3A2218; margin-bottom: 6px; }
    .sub { font-size: 0.88rem; color: #7A6E68; margin-bottom: 32px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 0.78rem; font-weight: 500; color: #7A6E68; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em; }
    .form-group input { width: 100%; padding: 13px 16px; border: 1.5px solid #EDE5D8; border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: 0.92rem; color: #3A2218; background: #FAF6F0; outline: none; transition: border-color .2s, box-shadow .2s; }
    .form-group input:focus { border-color: #8B1A1A; box-shadow: 0 0 0 3px rgba(139,26,26,0.1); background: #fff; }
    .alert { border-radius: 10px; padding: 12px 16px; font-size: 0.85rem; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
    .alert-erreur { background: #fce8e8; color: #8B1A1A; border: 1px solid rgba(139,26,26,0.2); }
    .alert-succes { background: #e8f5e9; color: #2E7D32; border: 1px solid rgba(46,125,50,0.2); }
    .btn-connexion { width: 100%; padding: 14px; background: #8B1A1A; color: #fff; border: none; border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: 0.95rem; font-weight: 500; cursor: pointer; transition: all .25s; margin-top: 8px; }
    .btn-connexion:hover { background: #B94040; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(139,26,26,0.3); }
    .forgot { text-align: right; margin-top: 8px; }
    .forgot a { font-size: 0.8rem; color: #7A6E68; text-decoration: none; }
    .forgot a:hover { color: #8B1A1A; }
    .divider { display: flex; align-items: center; gap: 12px; margin: 24px 0; color: #B0A8A0; font-size: 0.82rem; }
    .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #EDE5D8; }
    .btn-inscription { display: block; width: 100%; padding: 13px; background: transparent; color: #8B1A1A; border: 1.5px solid #8B1A1A; border-radius: 12px; font-family: 'DM Sans', sans-serif; font-size: 0.92rem; font-weight: 500; text-align: center; text-decoration: none; transition: all .25s; }
    .btn-inscription:hover { background: #fce8e8; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: #7A6E68; text-decoration: none; font-size: 0.82rem; margin-bottom: 32px; }
    .back-link:hover { color: #3A2218; }
    @media (max-width: 768px) { body { grid-template-columns: 1fr; } .left { display: none; } .right { padding: 40px 24px; } }
  </style>
</head>
<body>
<div class="left">
  <a href="index.php" class="left-logo">♡ Vie à deux</a>
  <h1>Bon retour parmi <em>nous</em> !</h1>
  <p>Des milliers de célibataires sincères vous attendent.</p>
  <div class="left-badges">
    <div class="left-badge"><div class="icon">✓</div><span>Profils vérifiés et authentiques</span></div>
    <div class="left-badge"><div class="icon">🔒</div><span>Connexion sécurisée et chiffrée</span></div>
    <div class="left-badge"><div class="icon">📞</div><span>Notre équipe vous accompagne</span></div>
  </div>
</div>
<div class="right">
  <div class="form-card">
    <a href="index.php" class="back-link">← Retour à l'accueil</a>
    <h2>Se connecter</h2>
    <p class="sub">Email ou téléphone + mot de passe</p>

    <?php if ($erreur): ?>
      <div class="alert alert-erreur">⚠ <?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>
    <?php if ($succes): ?>
      <div class="alert alert-succes">✓ <?= htmlspecialchars($succes) ?></div>
    <?php endif; ?>

    <form method="POST" action="connexion.php">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Email ou téléphone</label>
        <input type="text" name="identifiant" placeholder="ex: contact@mail.com ou +33 6..."
               value="<?= htmlspecialchars($_POST['identifiant'] ?? '') ?>" required autofocus/>
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <input type="password" name="mot_de_passe" placeholder="Votre mot de passe" required/>
      </div>
      <div class="forgot"><a href="mdp_oublie.php">Mot de passe oublié ?</a></div>
      <button type="submit" class="btn-connexion">Se connecter →</button>
    </form>
    <div class="divider">ou</div>
    <a href="index.php" class="btn-inscription">Créer un compte gratuitement</a>
  </div>
</div>
</body>
</html>
