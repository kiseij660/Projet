<?php
// ============================================================
//  parametres.php — Paramètres utilisateur
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

$user_id = $_SESSION['user_id'];
$succes  = '';
$erreur  = '';

// Récupérer les infos actuelles
$stmt = $pdo->prepare("SELECT * FROM inscriptions WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $user_id]);
$membre = $stmt->fetch(PDO::FETCH_ASSOC);

// ── ACTION : Modifier infos ──
if (isset($_POST['action']) && $_POST['action'] === 'infos') {
    $prenom  = htmlspecialchars(trim($_POST['prenom'] ?? ''));
    $ville   = htmlspecialchars(trim($_POST['ville'] ?? ''));
    $centres = htmlspecialchars(trim($_POST['centres_interet'] ?? ''));
    $cherche = htmlspecialchars(trim($_POST['je_cherche'] ?? ''));

    if (empty($prenom) || empty($ville)) {
        $erreur = 'Prénom et ville sont obligatoires.';
    } else {
        $pdo->prepare("UPDATE inscriptions SET prenom=:prenom, ville=:ville, centres_interet=:centres, je_cherche=:cherche WHERE id=:id")
            ->execute([':prenom'=>$prenom,':ville'=>$ville,':centres'=>$centres,':cherche'=>$cherche,':id'=>$user_id]);
        $_SESSION['prenom'] = $prenom;
        $succes = 'Informations mises à jour avec succès !';
        $membre['prenom'] = $prenom;
        $membre['ville']  = $ville;
        $membre['centres_interet'] = $centres;
        $membre['je_cherche'] = $cherche;
    }
}

// ── ACTION : Changer mot de passe ──
if (isset($_POST['action']) && $_POST['action'] === 'mdp') {
    $ancien  = $_POST['ancien_mdp'] ?? '';
    $nouveau = $_POST['nouveau_mdp'] ?? '';
    $confirm = $_POST['confirm_mdp'] ?? '';

    if (!password_verify($ancien, $membre['mot_de_passe'])) {
        $erreur = 'Ancien mot de passe incorrect.';
    } elseif (strlen($nouveau) < 8) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
    } elseif (!preg_match('/[A-Z]/', $nouveau)) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins une majuscule.';
    } elseif (!preg_match('/[0-9]/', $nouveau)) {
        $erreur = 'Le nouveau mot de passe doit contenir au moins un chiffre.';
    } elseif ($nouveau !== $confirm) {
        $erreur = 'Les nouveaux mots de passe ne correspondent pas.';
    } else {
        $hash = password_hash($nouveau, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE inscriptions SET mot_de_passe=:mdp WHERE id=:id")
            ->execute([':mdp'=>$hash,':id'=>$user_id]);
        $succes = 'Mot de passe modifié avec succès !';
    }
}

// ── ACTION : Supprimer compte ──
if (isset($_POST['action']) && $_POST['action'] === 'supprimer') {
    $confirm_supp = $_POST['confirm_suppression'] ?? '';
    if ($confirm_supp === 'SUPPRIMER') {
        $pdo->prepare("UPDATE inscriptions SET actif=0 WHERE id=:id")
            ->execute([':id'=>$user_id]);
        session_destroy();
        header('Location: index.html?compte=supprime');
        exit;
    } else {
        $erreur = 'Tapez exactement "SUPPRIMER" pour confirmer.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Paramètres — Vie à deux</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #FAF6F0; color: #3A2218; }

    nav {
      position: sticky; top: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5vw; height: 68px;
      background: rgba(139,26,26,0.97);
    }
    .nav-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; color: #fff; text-decoration: none; }
    .nav-right { display: flex; align-items: center; gap: 20px; }
    .nav-right a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; }
    .nav-right a:hover { color: #fff; }

    .main { max-width: 800px; margin: 40px auto; padding: 0 24px 60px; }

    .page-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem; font-weight: 600; color: #3A2218;
      margin-bottom: 6px;
    }
    .page-sub { color: #7A6E68; font-size: 0.9rem; margin-bottom: 36px; }

    .alert {
      border-radius: 12px; padding: 14px 18px;
      font-size: 0.88rem; margin-bottom: 24px;
      display: flex; align-items: center; gap: 10px;
    }
    .alert-succes { background: #e8f5e9; color: #2E7D32; border: 1px solid rgba(46,125,50,0.2); }
    .alert-erreur { background: #fce8e8; color: #8B1A1A; border: 1px solid rgba(139,26,26,0.2); }

    .card {
      background: #fff; border-radius: 18px;
      padding: 32px 36px; margin-bottom: 24px;
      box-shadow: 0 4px 24px rgba(58,34,24,0.08);
    }
    .card-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.3rem; font-weight: 600; color: #3A2218;
      margin-bottom: 4px; display: flex; align-items: center; gap: 10px;
    }
    .card-sub { font-size: 0.82rem; color: #7A6E68; margin-bottom: 24px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block; font-size: 0.78rem; font-weight: 500;
      color: #7A6E68; margin-bottom: 6px;
      text-transform: uppercase; letter-spacing: 0.06em;
    }
    .form-group input,
    .form-group select {
      width: 100%; padding: 12px 16px;
      border: 1.5px solid #EDE5D8; border-radius: 10px;
      font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
      color: #3A2218; background: #FAF6F0; outline: none;
      transition: border-color .2s;
    }
    .form-group input:focus, .form-group select:focus {
      border-color: #8B1A1A;
      box-shadow: 0 0 0 3px rgba(139,26,26,0.1);
      background: #fff;
    }
    .form-group input[readonly] { background: #f0ece6; color: #999; cursor: not-allowed; }

    .btn {
      padding: 12px 28px; border-radius: 10px; border: none;
      font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
      font-weight: 500; cursor: pointer; transition: all .2s;
    }
    .btn-primary { background: #8B1A1A; color: #fff; }
    .btn-primary:hover { background: #B94040; transform: translateY(-1px); }
    .btn-danger { background: #fce8e8; color: #8B1A1A; border: 1.5px solid rgba(139,26,26,0.3); }
    .btn-danger:hover { background: #8B1A1A; color: #fff; }

    /* Indicateur mdp */
    .mdp-bar-wrap { margin-top: 8px; }
    .mdp-bar-bg { height: 6px; background: #EDE5D8; border-radius: 10px; overflow: hidden; }
    .mdp-bar { height: 100%; width: 0%; border-radius: 10px; transition: all .3s; }
    .critere { font-size: 0.76rem; color: #B0A8A0; padding: 2px 0; }
    .critere.ok { color: #27ae60; }

    /* Zone danger */
    .danger-zone {
      border: 1.5px solid rgba(139,26,26,0.3);
      border-radius: 18px; padding: 32px 36px;
      background: #fff8f8;
    }
    .danger-zone .card-title { color: #8B1A1A; }

    .avatar-circle {
      width: 70px; height: 70px; border-radius: 50%;
      background: linear-gradient(135deg, #8B1A1A, #B94040);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Cormorant Garamond', serif;
      font-size: 2rem; color: #fff; font-weight: 600;
      margin-bottom: 16px;
    }
    .membre-info { font-size: 0.85rem; color: #7A6E68; margin-top: 4px; }

    @media (max-width: 600px) {
      .form-row { grid-template-columns: 1fr; }
      .card { padding: 24px 20px; }
    }
  </style>
</head>
<body>

<nav>
  <a href="profils.php" class="nav-logo">♡ Vie à deux</a>
  <div class="nav-right">
    <a href="profils.php">← Retour aux profils</a>
    <a href="deconnexion.php">Déconnexion</a>
  </div>
</nav>

<div class="main">
  <h1 class="page-title">⚙ Mes paramètres</h1>
  <p class="page-sub">Gérez vos informations personnelles et la sécurité de votre compte.</p>

  <?php if ($succes): ?>
    <div class="alert alert-succes">✓ <?= htmlspecialchars($succes) ?></div>
  <?php endif; ?>
  <?php if ($erreur): ?>
    <div class="alert alert-erreur">⚠ <?= htmlspecialchars($erreur) ?></div>
  <?php endif; ?>

  <!-- ── PROFIL ── -->
  <div class="card">
    <div class="avatar-circle"><?= strtoupper(substr($membre['prenom'], 0, 1)) ?></div>
    <div class="card-title">👤 Mes informations</div>
    <p class="card-sub">Modifiez votre profil visible par les autres membres.</p>

    <form method="POST">
      <input type="hidden" name="action" value="infos"/>
      <div class="form-row">
        <div class="form-group">
          <label>Prénom</label>
          <input type="text" name="prenom" value="<?= htmlspecialchars($membre['prenom']) ?>" required/>
        </div>
        <div class="form-group">
          <label>Nom</label>
          <input type="text" value="<?= htmlspecialchars($membre['nom']) ?>" readonly/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Ville</label>
          <input type="text" name="ville" value="<?= htmlspecialchars($membre['ville']) ?>" required/>
        </div>
        <div class="form-group">
          <label>Je cherche</label>
          <select name="je_cherche">
            <option <?= $membre['je_cherche']==='Un homme'?'selected':'' ?>>Un homme</option>
            <option <?= $membre['je_cherche']==='Une femme'?'selected':'' ?>>Une femme</option>
            <option <?= $membre['je_cherche']==='Peu importe'?'selected':'' ?>>Peu importe</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Centres d'intérêt</label>
        <input type="text" name="centres_interet" value="<?= htmlspecialchars($membre['centres_interet']) ?>" placeholder="Voyage, Cuisine, Sport..."/>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($membre['email']) ?>" readonly/>
      </div>
      <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
    </form>
  </div>

  <!-- ── MOT DE PASSE ── -->
  <div class="card">
    <div class="card-title">🔒 Changer le mot de passe</div>
    <p class="card-sub">Choisissez un mot de passe fort avec au moins 8 caractères, une majuscule et un chiffre.</p>

    <form method="POST">
      <input type="hidden" name="action" value="mdp"/>
      <div class="form-group">
        <label>Ancien mot de passe</label>
        <input type="password" name="ancien_mdp" placeholder="Votre mot de passe actuel" required/>
      </div>
      <div class="form-group">
        <label>Nouveau mot de passe</label>
        <input type="password" name="nouveau_mdp" id="mdp-new" placeholder="Min. 8 car. + majuscule + chiffre" required oninput="checkMdpNew()"/>
        <div class="mdp-bar-wrap" id="bar-wrap" style="display:none">
          <div class="mdp-bar-bg"><div class="mdp-bar" id="mdp-bar"></div></div>
          <div id="mdp-force" style="font-size:0.75rem;margin-top:4px;font-weight:500;"></div>
          <div style="margin-top:8px;background:#FAF6F0;border-radius:8px;padding:8px 12px;">
            <div class="critere" id="c-len">✗ Au moins 8 caractères</div>
            <div class="critere" id="c-maj">✗ Au moins une majuscule</div>
            <div class="critere" id="c-chif">✗ Au moins un chiffre</div>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label>Confirmer le nouveau mot de passe</label>
        <input type="password" name="confirm_mdp" id="confirm-new" placeholder="Répétez le nouveau mot de passe" required oninput="checkConfirmNew()"/>
        <div id="confirm-msg" style="font-size:0.78rem;margin-top:5px;"></div>
      </div>
      <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
    </form>
  </div>

  <!-- ── SUPPRIMER ── -->
  <div class="danger-zone">
    <div class="card-title">🗑 Supprimer mon compte</div>
    <p class="card-sub" style="color:#8B1A1A;">Cette action est irréversible. Toutes vos données seront supprimées.</p>
    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.')">
      <input type="hidden" name="action" value="supprimer"/>
      <div class="form-group">
        <label>Tapez <strong>SUPPRIMER</strong> pour confirmer</label>
        <input type="text" name="confirm_suppression" placeholder="SUPPRIMER" required/>
      </div>
      <button type="submit" class="btn btn-danger">Supprimer mon compte définitivement</button>
    </form>
  </div>
</div>

<script>
function checkMdpNew() {
  const mdp = document.getElementById('mdp-new').value;
  document.getElementById('bar-wrap').style.display = mdp ? '' : 'none';
  const len  = mdp.length >= 8;
  const maj  = /[A-Z]/.test(mdp);
  const chif = /[0-9]/.test(mdp);
  const score = [len, maj, chif].filter(Boolean).length;
  const configs = [
    {w:'0%',c:'#EDE5D8',t:''},
    {w:'33%',c:'#e74c3c',t:'Faible 🔴'},
    {w:'66%',c:'#f1c40f',t:'Moyen 🟡'},
    {w:'100%',c:'#27ae60',t:'Fort 🟢'},
  ];
  const cfg = configs[score];
  document.getElementById('mdp-bar').style.width = cfg.w;
  document.getElementById('mdp-bar').style.background = cfg.c;
  document.getElementById('mdp-force').textContent = cfg.t;
  document.getElementById('mdp-force').style.color = cfg.c;
  setC('c-len', len, '✓ Au moins 8 caractères', '✗ Au moins 8 caractères');
  setC('c-maj', maj, '✓ Au moins une majuscule', '✗ Au moins une majuscule');
  setC('c-chif', chif, '✓ Au moins un chiffre', '✗ Au moins un chiffre');
  checkConfirmNew();
}
function setC(id, ok, t1, t2) {
  const el = document.getElementById(id);
  el.textContent = ok ? t1 : t2;
  el.className = 'critere' + (ok ? ' ok' : '');
}
function checkConfirmNew() {
  const mdp = document.getElementById('mdp-new').value;
  const conf = document.getElementById('confirm-new').value;
  const msg = document.getElementById('confirm-msg');
  if (!conf) { msg.textContent = ''; return; }
  if (mdp === conf) { msg.textContent = '✓ Les mots de passe correspondent'; msg.style.color = '#27ae60'; }
  else { msg.textContent = '✗ Les mots de passe ne correspondent pas'; msg.style.color = '#e74c3c'; }
}
</script>
</body>
</html>
