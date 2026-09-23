<?php
// ============================================================
//  mdp_oublie.php — Mot de passe oublié
// ============================================================
session_start();
require_once 'csrf.php';

$host   = 'localhost';
$dbname = 'vieadeux';
$user   = 'root';
$pass   = '';

$message = '';
$type    = '';
$envoye  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifier_csrf();
    $telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''));

    if (empty($telephone)) {
        $message = 'Veuillez entrer votre numéro de téléphone.';
        $type    = 'erreur';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT id, prenom FROM inscriptions WHERE telephone = :tel AND actif = 1 LIMIT 1");
            $stmt->execute([':tel' => $telephone]);
            $membre = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($membre) {
                // Enregistrer la demande de reset
                $pdo->prepare("UPDATE inscriptions SET reset_demande = NOW() WHERE id = :id")
                    ->execute([':id' => $membre['id']]);

                $envoye  = true;
                $message = "Demande envoyée ! Notre équipe va vous rappeler au <strong>$telephone</strong> pour vous communiquer votre nouveau mot de passe.";
                $type    = 'succes';
            } else {
                $message = 'Aucun compte trouvé avec ce numéro de téléphone.';
                $type    = 'erreur';
            }
        } catch (PDOException $e) {
            $message = 'Erreur de connexion à la base de données.';
            $type    = 'erreur';
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
  <title>Mot de passe oublié — Vie à deux</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      background: #FAF6F0;
      display: flex; align-items: center; justify-content: center;
      padding: 24px;
    }

    .card {
      background: #fff; border-radius: 20px;
      padding: 48px 44px; width: 100%; max-width: 420px;
      box-shadow: 0 20px 60px rgba(58,34,24,0.12);
    }

    .logo {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.3rem; font-weight: 600; color: #8B1A1A;
      text-decoration: none; display: block; margin-bottom: 32px;
    }

    .icon-wrap {
      width: 64px; height: 64px; border-radius: 16px;
      background: linear-gradient(135deg, #fce8e8, #FAF6F0);
      border: 1px solid rgba(139,26,26,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.8rem; margin-bottom: 20px;
    }

    h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.8rem; font-weight: 600;
      color: #3A2218; margin-bottom: 8px;
    }
    .sub {
      font-size: 0.88rem; color: #7A6E68;
      line-height: 1.6; margin-bottom: 28px;
    }

    .form-group { margin-bottom: 18px; }
    .form-group label {
      display: block; font-size: 0.78rem; font-weight: 500;
      color: #7A6E68; margin-bottom: 6px;
      text-transform: uppercase; letter-spacing: 0.06em;
    }
    .form-group input {
      width: 100%; padding: 13px 16px;
      border: 1.5px solid #EDE5D8; border-radius: 12px;
      font-family: 'DM Sans', sans-serif; font-size: 0.92rem;
      color: #3A2218; background: #FAF6F0; outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-group input:focus {
      border-color: #8B1A1A;
      box-shadow: 0 0 0 3px rgba(139,26,26,0.1);
      background: #fff;
    }

    .alert {
      border-radius: 10px; padding: 14px 16px;
      font-size: 0.88rem; margin-bottom: 20px;
      display: flex; align-items: flex-start; gap: 10px;
      line-height: 1.5;
    }
    .alert-erreur { background: #fce8e8; color: #8B1A1A; border: 1px solid rgba(139,26,26,0.2); }
    .alert-succes { background: #e8f5e9; color: #2E7D32; border: 1px solid rgba(46,125,50,0.2); }

    .btn-submit {
      width: 100%; padding: 14px;
      background: #8B1A1A; color: #fff;
      border: none; border-radius: 12px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem; font-weight: 500;
      cursor: pointer; transition: all .25s;
    }
    .btn-submit:hover {
      background: #B94040;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(139,26,26,0.3);
    }
    .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .links { margin-top: 20px; text-align: center; }
    .links a {
      font-size: 0.83rem; color: #7A6E68; text-decoration: none;
      transition: color .2s;
    }
    .links a:hover { color: #8B1A1A; }
    .links span { color: #D4C5B0; margin: 0 8px; }

    /* Succès */
    .success-state { text-align: center; }
    .success-icon { font-size: 3rem; margin-bottom: 16px; }
    .success-state h2 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem; color: #3A2218; margin-bottom: 10px;
    }
    .success-state p { color: #7A6E68; font-size: 0.9rem; line-height: 1.7; margin-bottom: 24px; }
    .timeline {
      background: #FAF6F0; border-radius: 12px;
      padding: 16px 20px; margin-bottom: 24px; text-align: left;
    }
    .timeline-item {
      display: flex; gap: 12px; align-items: flex-start;
      font-size: 0.85rem; color: #5A4A3A;
      padding: 6px 0;
    }
    .timeline-item .dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: #8B1A1A; flex-shrink: 0; margin-top: 5px;
    }
    .btn-retour {
      display: block; padding: 13px;
      background: #8B1A1A; color: #fff;
      border-radius: 12px; text-decoration: none;
      font-size: 0.92rem; font-weight: 500;
      text-align: center; transition: all .25s;
    }
    .btn-retour:hover { background: #B94040; }
  </style>
</head>
<body>
<div class="card">
  <a href="index.php" class="logo">♡ Vie à deux</a>

  <?php if ($envoye): ?>
  <!-- ── SUCCÈS ── -->
  <div class="success-state">
    <div class="success-icon">📞</div>
    <h2>Demande envoyée !</h2>
    <p>Notre équipe va vous recontacter au numéro indiqué pour vous communiquer votre nouveau mot de passe.</p>
    <div class="timeline">
      <div class="timeline-item"><div class="dot"></div><span>Votre demande a été enregistrée</span></div>
      <div class="timeline-item"><div class="dot"></div><span>Notre équipe vous rappelle sous 24h</span></div>
      <div class="timeline-item"><div class="dot"></div><span>Vous recevrez votre nouveau mot de passe par téléphone</span></div>
    </div>
    <a href="connexion.php" class="btn-retour">Retour à la connexion</a>
  </div>

  <?php else: ?>
  <!-- ── FORMULAIRE ── -->
  <div class="icon-wrap">🔑</div>
  <h1>Mot de passe oublié</h1>
  <p class="sub">Entrez votre numéro de téléphone. Notre équipe vous rappellera pour vous communiquer un nouveau mot de passe.</p>

  <?php if ($message && $type === 'erreur'): ?>
    <div class="alert alert-erreur">⚠ <?= $message ?></div>
  <?php endif; ?>

  <form method="POST" action="mdp_oublie.php">
      <?php echo csrf_field(); ?>
    <div class="form-group">
      <label>Numéro de téléphone</label>
      <input type="tel" name="telephone"
             placeholder="+33 6 …"
             value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
             required autofocus/>
    </div>
    <button type="submit" class="btn-submit">Envoyer la demande →</button>
  </form>

  <div class="links">
    <a href="connexion.php">← Retour à la connexion</a>
    <span>|</span>
    <a href="index.php">Créer un compte</a>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
