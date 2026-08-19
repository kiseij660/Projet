<?php
// ============================================================
//  signaler.php — Signalement et blocage d'un membre
// ============================================================
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

$host   = 'localhost';
$dbname = 'vieadeux';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Erreur BDD.');
}

$user_id    = $_SESSION['user_id'];
$id_cible   = intval($_GET['id'] ?? 0);
$action     = $_GET['action'] ?? 'signaler'; // 'signaler' ou 'bloquer'
$succes     = '';
$erreur     = '';

// Vérifier que la cible existe
if (!$id_cible || $id_cible === $user_id) {
    header('Location: profils.php');
    exit;
}

$stmt = $pdo->prepare("SELECT prenom, nom FROM inscriptions WHERE id = :id AND actif = 1 LIMIT 1");
$stmt->execute([':id' => $id_cible]);
$cible = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cible) {
    header('Location: profils.php');
    exit;
}

// ── ACTION : Bloquer ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bloquer'])) {
    try {
        $pdo->prepare("INSERT IGNORE INTO blocages (id_bloqueur, id_bloque) VALUES (:bloqueur, :bloque)")
            ->execute([':bloqueur' => $user_id, ':bloque' => $id_cible]);
        $succes = "bloque";
    } catch (PDOException $e) {
        $erreur = 'Erreur lors du blocage.';
    }
}

// ── ACTION : Signaler ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signaler'])) {
    $raison      = htmlspecialchars(trim($_POST['raison'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));

    $raisons_valides = ['Comportement inapproprié','Faux profil','Contenu offensant','Spam','Harcèlement','Autre'];

    if (!in_array($raison, $raisons_valides)) {
        $erreur = 'Veuillez sélectionner une raison valide.';
    } else {
        $pdo->prepare("INSERT INTO signalements (id_signaleur, id_signale, raison, description) VALUES (:sig, :sig2, :raison, :desc)")
            ->execute([':sig'=>$user_id,':sig2'=>$id_cible,':raison'=>$raison,':desc'=>$description]);
        $succes = "signale";
    }
}

$prenom_cible = htmlspecialchars($cible['prenom']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="robots" content="noindex"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Signaler — Vie à deux</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #FAF6F0; color: #3A2218; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }

    .card {
      background: #fff; border-radius: 20px;
      padding: 44px 40px; width: 100%; max-width: 480px;
      box-shadow: 0 20px 60px rgba(58,34,24,0.12);
    }

    .back-link { display: inline-flex; align-items: center; gap: 6px; color: #7A6E68; text-decoration: none; font-size: 0.82rem; margin-bottom: 28px; }
    .back-link:hover { color: #3A2218; }

    .card-icon { font-size: 2.5rem; margin-bottom: 16px; }
    h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; font-weight: 600; color: #3A2218; margin-bottom: 6px; }
    .sub { font-size: 0.88rem; color: #7A6E68; margin-bottom: 28px; line-height: 1.6; }

    .tabs { display: flex; gap: 8px; margin-bottom: 28px; }
    .tab {
      flex: 1; padding: 10px; border-radius: 10px; border: 1.5px solid #EDE5D8;
      background: #FAF6F0; color: #7A6E68; font-size: 0.88rem;
      font-family: 'DM Sans', sans-serif; cursor: pointer; transition: all .2s; text-align: center;
    }
    .tab.active { background: #8B1A1A; color: #fff; border-color: #8B1A1A; }

    .section { display: none; }
    .section.active { display: block; }

    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 0.78rem; font-weight: 500; color: #7A6E68; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.06em; }
    .form-group select, .form-group textarea {
      width: 100%; padding: 12px 16px;
      border: 1.5px solid #EDE5D8; border-radius: 10px;
      font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
      color: #3A2218; background: #FAF6F0; outline: none;
      transition: border-color .2s;
    }
    .form-group select:focus, .form-group textarea:focus { border-color: #8B1A1A; background: #fff; box-shadow: 0 0 0 3px rgba(139,26,26,0.1); }
    .form-group textarea { height: 100px; resize: none; }

    .btn { width: 100%; padding: 13px; border-radius: 12px; border: none; font-family: 'DM Sans', sans-serif; font-size: 0.92rem; font-weight: 500; cursor: pointer; transition: all .2s; }
    .btn-danger { background: #8B1A1A; color: #fff; }
    .btn-danger:hover { background: #B94040; transform: translateY(-2px); }
    .btn-block { background: #3A2218; color: #fff; }
    .btn-block:hover { background: #5a3a28; transform: translateY(-2px); }

    .alert { border-radius: 12px; padding: 16px 20px; text-align: center; }
    .alert-succes { background: #e8f5e9; color: #2E7D32; border: 1px solid rgba(46,125,50,0.2); }

    .bloquer-info {
      background: #FAF6F0; border-radius: 12px; padding: 16px 20px;
      font-size: 0.85rem; color: #7A6E68; line-height: 1.6; margin-bottom: 20px;
    }
    .bloquer-info strong { color: #3A2218; }
  </style>
</head>
<body>
<div class="card">
  <a href="profils.php" class="back-link">← Retour aux profils</a>

  <?php if ($succes === 'signale'): ?>
    <div class="card-icon">✅</div>
    <h1>Signalement envoyé</h1>
    <p class="sub">Merci pour votre signalement. Notre équipe va examiner ce profil dans les plus brefs délais.</p>
    <a href="profils.php" style="display:block;text-align:center;padding:13px;background:#8B1A1A;color:#fff;border-radius:12px;text-decoration:none;font-size:0.92rem;">Retour aux profils</a>

  <?php elseif ($succes === 'bloque'): ?>
    <div class="card-icon">🚫</div>
    <h1><?= $prenom_cible ?> est bloqué(e)</h1>
    <p class="sub">Ce membre ne pourra plus voir votre profil et vous ne verrez plus le sien.</p>
    <a href="profils.php" style="display:block;text-align:center;padding:13px;background:#3A2218;color:#fff;border-radius:12px;text-decoration:none;font-size:0.92rem;">Retour aux profils</a>

  <?php else: ?>
    <div class="card-icon">⚠️</div>
    <h1>Signaler <?= $prenom_cible ?></h1>
    <p class="sub">Choisissez une action pour ce profil. Votre signalement reste confidentiel.</p>

    <!-- Onglets -->
    <div class="tabs">
      <div class="tab active" onclick="switchTab('signaler', this)">🚨 Signaler</div>
      <div class="tab" onclick="switchTab('bloquer', this)">🚫 Bloquer</div>
    </div>

    <!-- Signaler -->
    <div class="section active" id="section-signaler">
      <form method="POST">
        <div class="form-group">
          <label>Raison du signalement</label>
          <select name="raison" required>
            <option value="">Choisissez une raison…</option>
            <option>Comportement inapproprié</option>
            <option>Faux profil</option>
            <option>Contenu offensant</option>
            <option>Spam</option>
            <option>Harcèlement</option>
            <option>Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label>Description (optionnel)</label>
          <textarea name="description" placeholder="Décrivez brièvement ce qui s'est passé…"></textarea>
        </div>
        <button type="submit" name="signaler" class="btn btn-danger">Envoyer le signalement</button>
      </form>
    </div>

    <!-- Bloquer -->
    <div class="section" id="section-bloquer">
      <div class="bloquer-info">
        En bloquant <strong><?= $prenom_cible ?></strong> :
        <br>• Ce membre ne verra plus votre profil
        <br>• Vous ne verrez plus son profil
        <br>• Cette action peut être annulée dans vos paramètres
      </div>
      <form method="POST">
        <button type="submit" name="bloquer" class="btn btn-block">🚫 Bloquer <?= $prenom_cible ?></button>
      </form>
    </div>
  <?php endif; ?>
</div>

<script>
function switchTab(section, el) {
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('section-' + section).classList.add('active');
}
</script>
</body>
</html>
