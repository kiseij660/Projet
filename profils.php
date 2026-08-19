<?php
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
    die('Erreur BDD : ' . $e->getMessage());
}

// ── Définir AVANT d'utiliser ──
$user_id     = $_SESSION['user_id'];
$prenom_user = $_SESSION['prenom'];

$stmt = $pdo->prepare("SELECT id, prenom, nom, je_suis, ville, date_naissance, centres_interet, photo, 
    TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) AS age 
    FROM inscriptions 
    WHERE actif = 1 AND id != :id
    ORDER BY date_inscription DESC");
$stmt->execute([':id' => $user_id]);
$membres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="robots" content="noindex"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Découvrir des profils — Vie à deux</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #FAF6F0; color: #3A2218; }
    nav {
      position: sticky; top: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5vw; height: 68px;
      background: rgba(139,26,26,0.97); backdrop-filter: blur(10px);
    }
    .nav-logo { font-family: 'Cormorant Garamond', serif; font-size: 1.4rem; font-weight: 600; color: #fff; text-decoration: none; }
    .nav-right { display: flex; align-items: center; gap: 20px; }
    .nav-right a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; transition: color .2s; }
    .nav-right a:hover { color: #fff; }
    .nav-avatar { width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 0.85rem; font-weight: 600; }
    .welcome-banner { background: linear-gradient(135deg, #8B1A1A, #3A2218); padding: 28px 5vw; color: #fff; }
    .welcome-banner h1 { font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; font-weight: 400; }
    .welcome-banner h1 em { font-style: italic; color: #f0c0c0; }
    .welcome-banner p { color: rgba(255,255,255,0.65); font-size: 0.88rem; margin-top: 4px; }
    .filters-bar { background: #fff; border-bottom: 1px solid #EDE5D8; padding: 14px 5vw; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
    .filters-bar select { padding: 9px 14px; border: 1.5px solid #EDE5D8; border-radius: 40px; font-size: 0.85rem; font-family: 'DM Sans', sans-serif; background: #FAF6F0; color: #3A2218; outline: none; cursor: pointer; }
    .filters-bar select:focus { border-color: #8B1A1A; }
    .filter-label { font-size: 0.8rem; color: #7A6E68; font-weight: 500; }
    .count-info { margin-left: auto; font-size: 0.82rem; color: #7A6E68; }
    .main { max-width: 1200px; margin: 0 auto; padding: 36px 5vw; }
    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 24px; }
    .empty-state { grid-column: 1/-1; text-align: center; padding: 60px; color: #B0A8A0; }
    .profile-card { background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 24px rgba(58,34,24,0.08); transition: transform .3s, box-shadow .3s; }
    .profile-card:hover { transform: translateY(-6px); box-shadow: 0 16px 48px rgba(58,34,24,0.15); }
    .profile-badge { position: absolute; top: 12px; left: 12px; background: rgba(139,26,26,0.85); color: #fff; font-size: 0.7rem; padding: 4px 10px; border-radius: 40px; }
    .like-btn { position: absolute; bottom: 12px; right: 12px; width: 38px; height: 38px; border-radius: 50%; background: rgba(255,255,255,0.9); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; transition: all .2s; box-shadow: 0 2px 8px rgba(0,0,0,0.15); }
    .like-btn:hover, .like-btn.liked { background: #8B1A1A; color: #fff; transform: scale(1.1); }
    .profile-info { padding: 16px 18px 18px; }
    .profile-name { font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; font-weight: 600; color: #3A2218; }
    .profile-meta { font-size: 0.82rem; color: #7A6E68; margin-top: 3px; }
    .profile-tags { display: flex; gap: 5px; flex-wrap: wrap; margin-top: 8px; }
    .tag { font-size: 0.7rem; padding: 3px 8px; border-radius: 20px; background: #FAF6F0; color: #7A6E68; border: 1px solid #EDE5D8; }
    .profile-cta { margin-top: 12px; background: linear-gradient(135deg, #fce8e8, #FAF6F0); border: 1px solid rgba(139,26,26,0.15); border-radius: 10px; padding: 10px 14px; text-align: center; }
    .cta-text { font-size: 0.85rem; color: #8B1A1A; font-weight: 500; }
    .cta-sub { font-size: 0.73rem; color: #7A6E68; margin-top: 2px; }
    .action-btns { display: flex; gap: 6px; margin-top: 8px; }
    .action-btns a { flex: 1; text-align: center; padding: 7px; border-radius: 8px; font-size: 0.75rem; text-decoration: none; transition: all .2s; border: 1px solid; }
    .btn-signaler { color: #8B1A1A; background: #fce8e8; border-color: rgba(139,26,26,0.2); }
    .btn-signaler:hover { background: #8B1A1A; color: #fff; }
    .btn-bloquer { color: #3A2218; background: #FAF6F0; border-color: #EDE5D8; }
    .btn-bloquer:hover { background: #3A2218; color: #fff; }
    @media (max-width: 600px) { .grid { grid-template-columns: 1fr 1fr; gap: 14px; } }
  </style>
</head>
<body>

<nav>
  <a href="profils.php" class="nav-logo">♡ Vie à deux</a>
  <div class="nav-right">
    <span style="color:rgba(255,255,255,0.7);font-size:0.85rem">Bonjour, <?= htmlspecialchars($prenom_user) ?> 👋</span>
    <a href="parametres.php">⚙ Paramètres</a>
    <a href="deconnexion.php" style="color:rgba(255,255,255,0.6)!important">Déconnexion</a>
    <div class="nav-avatar"><?= strtoupper(substr($prenom_user, 0, 1)) ?></div>
  </div>
</nav>

<div class="welcome-banner">
  <h1>Bonjour <em><?= htmlspecialchars($prenom_user) ?></em> !</h1>
  <p><?= count($membres) ?> profil<?= count($membres) > 1 ? 's' : '' ?> vous attend<?= count($membres) > 1 ? 'ent' : '' ?></p>
</div>

<div class="filters-bar">
  <span class="filter-label">Filtrer :</span>
  <select id="f-genre" onchange="filtrer()">
    <option value="">Tous les profils</option>
    <option value="Une femme">Femmes</option>
    <option value="Un homme">Hommes</option>
  </select>
  <select id="f-age" onchange="filtrer()">
    <option value="">Tous les âges</option>
    <option value="18-29">18 – 29 ans</option>
    <option value="30-39">30 – 39 ans</option>
    <option value="40-55">40 – 55 ans</option>
    <option value="55+">55 ans et +</option>
  </select>
  <span class="count-info" id="count-info"><?= count($membres) ?> profil<?= count($membres) > 1 ? 's' : '' ?> trouvé<?= count($membres) > 1 ? 's' : '' ?></span>
</div>

<div class="main">
  <div style="background:#fff;border-left:4px solid #8B1A1A;border-radius:0 12px 12px 0;padding:16px 22px;margin-bottom:28px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 16px rgba(58,34,24,0.08);">
    <div style="font-size:1.5rem">📞</div>
    <div>
      <h4 style="font-size:0.92rem;color:#3A2218;margin-bottom:2px;">Comment ça marche ?</h4>
      <p style="font-size:0.8rem;color:#7A6E68;">Parcourez les profils, repérez vos coups de cœur. Notre équipe vous rappelle sous 24h.</p>
    </div>
  </div>

  <div class="grid" id="grid">
    <?php if (empty($membres)): ?>
      <div class="empty-state">Aucun autre membre pour l'instant. Revenez bientôt !</div>
    <?php else: ?>
      <?php foreach ($membres as $m): ?>
        <div class="profile-card" data-genre="<?= htmlspecialchars($m['je_suis']) ?>" data-age="<?= $m['age'] ?>">
          <div style="position:relative;aspect-ratio:3/4;overflow:hidden;">
            <?php if (!empty($m['photo'])): ?>
              <img src="<?= htmlspecialchars($m['photo']) ?>" alt="<?= htmlspecialchars($m['prenom']) ?>" style="width:100%;height:100%;object-fit:cover;display:block;" loading="lazy"/>
            <?php else: ?>
              <div style="width:100%;height:100%;background:linear-gradient(135deg,#EDE5D8,#D4C5B0);display:flex;align-items:center;justify-content:center;font-size:4rem;color:#8B1A1A;font-family:'Cormorant Garamond',serif;">
                <?= strtoupper(substr($m['prenom'], 0, 1)) ?>
              </div>
            <?php endif; ?>
            <span class="profile-badge"><?= $m['je_suis'] === 'Une femme' ? '♀' : '♂' ?> <?= $m['age'] ?> ans</span>
            <button class="like-btn" onclick="toggleLike(this)">♡</button>
          </div>
          <div class="profile-info">
            <div class="profile-name"><?= htmlspecialchars($m['prenom']) ?>, <?= $m['age'] ?> ans</div>
            <div class="profile-meta">📍 <?= htmlspecialchars($m['ville']) ?></div>
            <?php if (!empty($m['centres_interet'])): ?>
              <div class="profile-tags">
                <?php foreach (explode(',', $m['centres_interet']) as $tag): ?>
                  <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <div class="profile-cta">
              <div class="cta-text">💌 Intéressé(e) ?</div>
              <div class="cta-sub">Notre équipe vous contacte sous 24h</div>
            </div>
            <div class="action-btns">
              <a href="signaler.php?id=<?= $m['id'] ?>" class="btn-signaler">🚨 Signaler</a>
              <a href="signaler.php?id=<?= $m['id'] ?>&action=bloquer" class="btn-bloquer">🚫 Bloquer</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
function filtrer() {
  const genre = document.getElementById('f-genre').value;
  const age   = document.getElementById('f-age').value;
  const cards = document.querySelectorAll('.profile-card');
  let visible = 0;
  cards.forEach(card => {
    const g = card.dataset.genre;
    const a = parseInt(card.dataset.age);
    let show = true;
    if (genre && g !== genre) show = false;
    if (age) {
      if (age === '55+' && a < 55) show = false;
      else if (age !== '55+') {
        const [min, max] = age.split('-').map(Number);
        if (a < min || a > max) show = false;
      }
    }
    card.style.display = show ? '' : 'none';
    if (show) visible++;
  });
  document.getElementById('count-info').textContent = `${visible} profil${visible > 1 ? 's' : ''} trouvé${visible > 1 ? 's' : ''}`;
}

function toggleLike(btn) {
  btn.classList.toggle('liked');
  btn.textContent = btn.classList.contains('liked') ? '♥' : '♡';
}
</script>
</body>
</html>
