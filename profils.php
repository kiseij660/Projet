<?php
// ============================================================
//  profils.php — Page des profils (session requise)
// ============================================================
session_start();

// Rediriger si pas connecté
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

// Récupérer tous les membres sauf l'utilisateur connecté
$stmt = $pdo->prepare("SELECT id, prenom, nom, je_suis, ville, age FROM inscriptions WHERE actif = 1 AND id != :id ORDER BY date_inscription DESC");
$stmt->execute([':id' => $_SESSION['user_id']]);
$membres = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prenom_user = $_SESSION['prenom'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Découvrir des profils — Vie à deux</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #FAF6F0; color: #3A2218; }

    /* ── NAV ── */
    nav {
      position: sticky; top: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5vw; height: 68px;
      background: rgba(139,26,26,0.97);
      backdrop-filter: blur(10px);
    }
    .nav-logo {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.4rem; font-weight: 600; color: #fff;
      text-decoration: none;
    }
    .nav-right { display: flex; align-items: center; gap: 20px; }
    .nav-right a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 0.88rem; transition: color .2s; }
    .nav-right a:hover { color: #fff; }
    .nav-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: rgba(255,255,255,0.2);
      border: 2px solid rgba(255,255,255,0.4);
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 0.85rem; font-weight: 600;
      cursor: pointer; text-decoration: none;
    }
    .nav-logout {
      font-size: 0.82rem; color: rgba(255,255,255,0.6) !important;
    }

    /* ── BANNER ── */
    .welcome-banner {
      background: linear-gradient(135deg, #8B1A1A, #3A2218);
      padding: 28px 5vw; color: #fff;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
    }
    .welcome-banner h1 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.6rem; font-weight: 400;
    }
    .welcome-banner h1 em { font-style: italic; color: #f0c0c0; }
    .welcome-banner p { color: rgba(255,255,255,0.65); font-size: 0.88rem; margin-top: 4px; }

    /* ── FILTRES ── */
    .filters-bar {
      background: #fff; border-bottom: 1px solid #EDE5D8;
      padding: 14px 5vw;
      display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
    }
    .filters-bar select {
      padding: 9px 14px; border: 1.5px solid #EDE5D8;
      border-radius: 40px; font-size: 0.85rem;
      font-family: 'DM Sans', sans-serif;
      background: #FAF6F0; color: #3A2218;
      outline: none; cursor: pointer;
      transition: border-color .2s;
    }
    .filters-bar select:focus { border-color: #8B1A1A; }
    .filter-label { font-size: 0.8rem; color: #7A6E68; font-weight: 500; }
    .count-info { margin-left: auto; font-size: 0.82rem; color: #7A6E68; }

    /* ── GRILLE ── */
    .main { max-width: 1200px; margin: 0 auto; padding: 36px 5vw; }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      gap: 24px;
    }
    .empty-state {
      grid-column: 1/-1; text-align: center;
      padding: 60px; color: #B0A8A0; font-size: 0.95rem;
    }

    /* ── CARD PROFIL ── */
    .profile-card {
      background: #fff; border-radius: 18px; overflow: hidden;
      box-shadow: 0 4px 24px rgba(58,34,24,0.08);
      transition: transform .3s, box-shadow .3s;
    }
    .profile-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 16px 48px rgba(58,34,24,0.15);
    }
    .profile-avatar {
      width: 100%; aspect-ratio: 1;
      background: linear-gradient(135deg, #EDE5D8, #D4C5B0);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Cormorant Garamond', serif;
      font-size: 4rem; color: #8B1A1A; position: relative;
    }
    .profile-badge {
      position: absolute; top: 12px; left: 12px;
      background: rgba(139,26,26,0.85); color: #fff;
      font-size: 0.7rem; padding: 4px 10px; border-radius: 40px;
    }
    .like-btn {
      position: absolute; bottom: 12px; right: 12px;
      width: 38px; height: 38px; border-radius: 50%;
      background: rgba(255,255,255,0.9); border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; transition: all .2s;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .like-btn:hover { background: #8B1A1A; color: #fff; transform: scale(1.1); }
    .like-btn.liked { background: #8B1A1A; color: #fff; }

    .profile-info { padding: 18px 20px 20px; }
    .profile-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.2rem; font-weight: 600; color: #3A2218;
    }
    .profile-meta { font-size: 0.82rem; color: #7A6E68; margin-top: 3px; }

    .profile-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 14px; }
    .btn-action {
      padding: 9px; border-radius: 10px; font-size: 0.82rem;
      font-family: 'DM Sans', sans-serif; cursor: pointer;
      transition: all .2s; font-weight: 500;
      text-align: center; border: none;
    }
    .btn-primary { background: #8B1A1A; color: #fff; }
    .btn-primary:hover { background: #B94040; }
    .btn-secondary { background: #FAF6F0; color: #3A2218; border: 1.5px solid #EDE5D8; }
    .btn-secondary:hover { background: #EDE5D8; }

    /* ── MODAL MESSAGE ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.5); z-index: 200;
      align-items: center; justify-content: center;
      backdrop-filter: blur(4px);
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: #fff; border-radius: 20px;
      width: 90%; max-width: 460px;
      padding: 36px; position: relative;
      box-shadow: 0 32px 80px rgba(0,0,0,0.25);
      animation: popIn .3s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes popIn {
      from { transform: scale(0.85); opacity: 0; }
      to   { transform: scale(1);    opacity: 1; }
    }
    .modal h3 {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.5rem; color: #3A2218; margin-bottom: 6px;
    }
    .modal p.sub { font-size: 0.85rem; color: #7A6E68; margin-bottom: 20px; }
    .modal textarea {
      width: 100%; height: 120px;
      border: 1.5px solid #EDE5D8; border-radius: 12px;
      padding: 14px; font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem; color: #3A2218; resize: none; outline: none;
      background: #FAF6F0; transition: border-color .2s;
    }
    .modal textarea:focus { border-color: #8B1A1A; background: #fff; }
    .modal-actions { display: flex; gap: 10px; margin-top: 16px; }
    .modal-btn {
      flex: 1; padding: 12px; border-radius: 12px; border: none;
      font-family: 'DM Sans', sans-serif; font-size: 0.9rem;
      font-weight: 500; cursor: pointer; transition: all .2s;
    }
    .modal-btn-send { background: #8B1A1A; color: #fff; }
    .modal-btn-send:hover { background: #B94040; }
    .modal-btn-cancel { background: #FAF6F0; color: #3A2218; border: 1.5px solid #EDE5D8; }
    .modal-btn-cancel:hover { background: #EDE5D8; }
    .modal-success {
      display: none; text-align: center; padding: 20px 0;
    }
    .modal-success .check { font-size: 2.5rem; margin-bottom: 10px; }
    .modal-success p { color: #3A2218; font-size: 0.95rem; }

    /* ── MODAL PROFIL ── */
    .modal-profil-info { margin-bottom: 20px; }
    .modal-profil-info .big-avatar {
      width: 80px; height: 80px; border-radius: 50%;
      background: linear-gradient(135deg, #EDE5D8, #D4C5B0);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.5rem; margin: 0 auto 14px;
    }
    .info-row {
      display: flex; justify-content: space-between;
      padding: 10px 0; border-bottom: 1px solid #f0ece6;
      font-size: 0.88rem;
    }
    .info-row span:first-child { color: #7A6E68; }
    .info-row span:last-child { color: #3A2218; font-weight: 500; }

    @media (max-width: 600px) {
      .grid { grid-template-columns: 1fr 1fr; gap: 14px; }
    }
  </style>
</head>
<body>

<nav>
  <a href="profils.php" class="nav-logo">♡ Vie à deux</a>
  <div class="nav-right">
    <span style="color:rgba(255,255,255,0.7);font-size:0.85rem">Bonjour, <?= htmlspecialchars($prenom_user) ?> 👋</span>
    <a href="deconnexion.php" class="nav-logout">Déconnexion</a>
    <a href="#" class="nav-avatar"><?= strtoupper(substr($prenom_user, 0, 1)) ?></a>
  </div>
</nav>

<div class="welcome-banner">
  <div>
    <h1>Bonjour <em><?= htmlspecialchars($prenom_user) ?></em> !</h1>
    <p><?= count($membres) ?> membre<?= count($membres) > 1 ? 's' : '' ?> vous attend<?= count($membres) > 1 ? 'ent' : '' ?></p>
  </div>
</div>

<!-- FILTRES -->
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

<!-- GRILLE -->
<div class="main">
  <div class="grid" id="grid">
    <?php if (empty($membres)): ?>
      <div class="empty-state">Aucun autre membre pour l'instant. Revenez bientôt !</div>
    <?php else: ?>
      <?php foreach ($membres as $m): ?>
        <div class="profile-card"
             data-genre="<?= htmlspecialchars($m['je_suis']) ?>"
             data-age="<?= $m['age'] ?>">
          <div class="profile-avatar">
            <?= strtoupper(substr($m['prenom'], 0, 1)) ?>
            <span class="profile-badge"><?= $m['je_suis'] === 'Une femme' ? '♀' : '♂' ?> <?= $m['age'] ?> ans</span>
            <button class="like-btn" onclick="toggleLike(this)" title="J'aime">♡</button>
          </div>
          <div class="profile-info">
            <div class="profile-name"><?= htmlspecialchars($m['prenom']) ?>, <?= $m['age'] ?> ans</div>
            <div class="profile-meta">📍 <?= htmlspecialchars($m['ville']) ?></div>
            <div class="profile-actions">
              <button class="btn-action btn-primary"
                onclick="openMessage(<?= $m['id'] ?>, '<?= htmlspecialchars($m['prenom']) ?>')">
                💬 Message
              </button>
              <button class="btn-action btn-secondary"
                onclick="openProfil(<?= $m['id'] ?>, '<?= htmlspecialchars($m['prenom']) ?>', '<?= htmlspecialchars($m['nom']) ?>', '<?= $m['age'] ?>', '<?= htmlspecialchars($m['ville']) ?>', '<?= htmlspecialchars($m['je_suis']) ?>')">
                👤 Profil
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL MESSAGE -->
<div class="modal-overlay" id="modal-msg" onclick="closeIfOutside(event, 'modal-msg')">
  <div class="modal">
    <h3 id="msg-titre">Envoyer un message</h3>
    <p class="sub" id="msg-sub">Écrivez votre premier message</p>
    <div id="msg-form">
      <textarea id="msg-texte" placeholder="Bonjour ! J'ai vu votre profil et…"></textarea>
      <div class="modal-actions">
        <button class="modal-btn modal-btn-send" onclick="envoyerMessage()">Envoyer ✉</button>
        <button class="modal-btn modal-btn-cancel" onclick="fermerModal('modal-msg')">Annuler</button>
      </div>
    </div>
    <div class="modal-success" id="msg-success">
      <div class="check">✅</div>
      <p>Message envoyé avec succès !<br><small style="color:#7A6E68">Fonctionnalité complète bientôt disponible.</small></p>
    </div>
  </div>
</div>

<!-- MODAL PROFIL -->
<div class="modal-overlay" id="modal-profil" onclick="closeIfOutside(event, 'modal-profil')">
  <div class="modal">
    <h3>Fiche profil</h3>
    <div class="modal-profil-info">
      <div class="big-avatar" id="profil-avatar"></div>
      <div id="profil-details"></div>
    </div>
    <div class="modal-actions">
      <button class="modal-btn modal-btn-send" onclick="ouvrirDepuisProfil()">💬 Envoyer un message</button>
      <button class="modal-btn modal-btn-cancel" onclick="fermerModal('modal-profil')">Fermer</button>
    </div>
  </div>
</div>

<script>
let currentMemberId = null;
let currentMembrePrenom = null;

// ── FILTRES ──
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

  document.getElementById('count-info').textContent =
    `${visible} profil${visible > 1 ? 's' : ''} trouvé${visible > 1 ? 's' : ''}`;
}

// ── LIKE ──
function toggleLike(btn) {
  btn.classList.toggle('liked');
  btn.textContent = btn.classList.contains('liked') ? '♥' : '♡';
}

// ── MESSAGE ──
function openMessage(id, prenom) {
  currentMemberId = id;
  currentMembrePrenom = prenom;
  document.getElementById('msg-titre').textContent = `Message à ${prenom}`;
  document.getElementById('msg-sub').textContent = `Écrivez votre premier message à ${prenom}`;
  document.getElementById('msg-texte').value = '';
  document.getElementById('msg-form').style.display = '';
  document.getElementById('msg-success').style.display = 'none';
  document.getElementById('modal-msg').classList.add('open');
}

function envoyerMessage() {
  const texte = document.getElementById('msg-texte').value.trim();
  if (!texte) { alert('Écrivez un message avant d\'envoyer !'); return; }
  // Simulation envoi (à connecter à une vraie table messages)
  document.getElementById('msg-form').style.display = 'none';
  document.getElementById('msg-success').style.display = 'block';
  setTimeout(() => fermerModal('modal-msg'), 2500);
}

// ── PROFIL ──
function openProfil(id, prenom, nom, age, ville, genre) {
  currentMemberId = id;
  currentMembrePrenom = prenom;
  document.getElementById('profil-avatar').textContent = prenom.charAt(0).toUpperCase();
  document.getElementById('profil-details').innerHTML = `
    <div class="info-row"><span>Prénom & Nom</span><span>${prenom} ${nom}</span></div>
    <div class="info-row"><span>Genre</span><span>${genre}</span></div>
    <div class="info-row"><span>Âge</span><span>${age} ans</span></div>
    <div class="info-row"><span>Ville</span><span>${ville}</span></div>
  `;
  document.getElementById('modal-profil').classList.add('open');
}

function ouvrirDepuisProfil() {
  fermerModal('modal-profil');
  setTimeout(() => openMessage(currentMemberId, currentMembrePrenom), 200);
}

// ── UTILS ──
function fermerModal(id) {
  document.getElementById(id).classList.remove('open');
}
function closeIfOutside(e, id) {
  if (e.target === document.getElementById(id)) fermerModal(id);
}
</script>
</body>
</html>
