<?php
// ============================================================
//  admin/index.php — Panneau d'administration sécurisé
//  Accès : http://localhost/vieadeux/admin/
// ============================================================

// ── Authentification simple ──────────────────────────────────
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'vieadeux2024'; // À changer absolument !

// ── Configuration session sécurisée ──
ini_set('session.cookie_httponly', 1);  // HttpOnly — JS ne peut pas lire le cookie
ini_set('session.cookie_samesite', 'Strict');
// ini_set('session.cookie_secure', 1); // Décommenter en HTTPS

session_start();

// ── Rate Limiting — bloquer brute force ──
$ip         = $_SERVER['REMOTE_ADDR'];
$cle_essais = 'admin_essais_' . md5($ip);
$cle_blocage= 'admin_blocage_' . md5($ip);

if (!isset($_SESSION[$cle_essais]))  $_SESSION[$cle_essais]  = 0;
if (!isset($_SESSION[$cle_blocage])) $_SESSION[$cle_blocage] = 0;

$est_bloque  = false;
$temps_restant = 0;

if ($_SESSION[$cle_blocage] > time()) {
    $est_bloque    = true;
    $temps_restant = $_SESSION[$cle_blocage] - time();
}

if (isset($_POST['login']) && !$est_bloque) {
    if ($_POST['user'] === $ADMIN_USER && $_POST['pass'] === $ADMIN_PASS) {
        // ✅ Connexion réussie
        $_SESSION[$cle_essais] = 0;  // Réinitialiser les essais
        session_regenerate_id(true); // 🔒 Prévenir la session fixation
        $_SESSION['admin'] = true;
    } else {
        // ❌ Échec — incrémenter et bloquer
        $_SESSION[$cle_essais]++;
        $essais = $_SESSION[$cle_essais];

        // Bloquer seulement après 3 échecs
        $erreur_login = true;
        $tentatives_restantes = max(0, 3 - $essais);

        if ($essais >= 3) {
            // Durée de blocage exponentielle après 3 échecs
            $bloc = $essais - 2; // 1, 2, 3...
            $durees = [1 => 60, 2 => 180, 3 => 300, 4 => 600];
            $duree  = $durees[min($bloc, 4)] ?? 600;
            $_SESSION[$cle_blocage] = time() + $duree;
        }
    }
} elseif (isset($_POST['login']) && $est_bloque) {
    $erreur_login = true;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$connecte = !empty($_SESSION['admin']);

// ── Connexion BDD ────────────────────────────────────────────
if ($connecte) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=vieadeux;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die('<p style="color:red">Erreur BDD : ' . $e->getMessage() . '</p>');
    }

    // Filtres
    $search = htmlspecialchars(trim($_GET['q'] ?? ''));
    $filtre_genre = $_GET['genre'] ?? '';
    $ordre = in_array($_GET['ordre'] ?? '', ['date_inscription','age','ville']) ? $_GET['ordre'] : 'date_inscription';

    $sql = "SELECT *, TIMESTAMPDIFF(YEAR, date_naissance, CURDATE()) AS age FROM inscriptions WHERE actif = 1";
    $params = [];
    if ($search) {
        $sql .= " AND (ville LIKE :q OR telephone LIKE :q)";
        $params[':q'] = "%$search%";
    }
    if ($filtre_genre) {
        $sql .= " AND je_suis = :genre";
        $params[':genre'] = $filtre_genre;
    }
    $sql .= " ORDER BY $ordre DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ── Action désactiver/réactiver membre ──
    if (isset($_GET['desactiver'])) {
        $pdo->prepare("UPDATE inscriptions SET actif=0 WHERE id=:id")
            ->execute([':id' => intval($_GET['desactiver'])]);
        header('Location: index.php');
        exit;
    }
    if (isset($_GET['reactiver'])) {
        $pdo->prepare("UPDATE inscriptions SET actif=1 WHERE id=:id")
            ->execute([':id' => intval($_GET['reactiver'])]);
        header('Location: index.php');
        exit;
    }
    if (isset($_GET['supprimer'])) {
        $pdo->prepare("DELETE FROM inscriptions WHERE id=:id")
            ->execute([':id' => intval($_GET['supprimer'])]);
        header('Location: index.php');
        exit;
    }

    // ── Stats
    $total  = $pdo->query("SELECT COUNT(*) FROM inscriptions")->fetchColumn();
    $femmes = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE je_suis='Une femme' AND actif=1")->fetchColumn();
    $hommes = $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE je_suis='Un homme' AND actif=1")->fetchColumn();
    $semaine= $pdo->query("SELECT COUNT(*) FROM inscriptions WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND actif=1")->fetchColumn();
    $signalements = $pdo->query("SELECT COUNT(*) FROM signalements WHERE traite=0")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
    <meta name="robots" content="noindex, nofollow"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Administration — Vie à deux</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #f5f3ef; color: #2d2417; min-height: 100vh; }

    /* ── LOGIN ── */
    .login-wrap {
      min-height: 100vh; display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, #8B1A1A, #3A2218);
    }
    .login-card {
      background: #fff; border-radius: 16px; padding: 48px 40px;
      width: 360px; box-shadow: 0 24px 60px rgba(0,0,0,0.3);
    }
    .login-card h1 { font-size: 1.5rem; margin-bottom: 6px; color: #3A2218; }
    .login-card p  { color: #888; font-size: 0.85rem; margin-bottom: 28px; }
    .login-card input {
      width: 100%; padding: 12px 14px; margin-bottom: 14px;
      border: 1.5px solid #e0d8cc; border-radius: 10px;
      font-size: 0.95rem; outline: none;
      transition: border-color .2s;
    }
    .login-card input:focus { border-color: #8B1A1A; }
    .btn-login {
      width: 100%; padding: 13px; background: #8B1A1A; color: #fff;
      border: none; border-radius: 10px; font-size: 0.95rem;
      cursor: pointer; transition: background .2s;
    }
    .btn-login:hover { background: #B94040; }
    .erreur { background: #fce8e8; color: #8B1A1A; padding: 10px 14px;
              border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; }

    /* ── LAYOUT ── */
    .sidebar {
      position: fixed; left: 0; top: 0; bottom: 0; width: 220px;
      background: #3A2218; color: rgba(255,255,255,0.8);
      padding: 32px 20px; display: flex; flex-direction: column; gap: 8px;
    }
    .sidebar .logo { font-size: 1.2rem; color: #fff; margin-bottom: 24px; font-weight: 600; }
    .sidebar a {
      display: block; padding: 10px 14px; border-radius: 8px;
      color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.88rem;
      transition: background .2s;
    }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.12); color: #fff; }
    .sidebar .logout { margin-top: auto; }

    .main { margin-left: 220px; padding: 32px; }

    /* ── STATS ── */
    .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 32px; }
    .stat-card {
      background: #fff; border-radius: 14px; padding: 22px 20px;
      box-shadow: 0 4px 20px rgba(58,34,24,0.08);
    }
    .stat-card .num { font-size: 2.2rem; font-weight: 700; color: #8B1A1A; line-height: 1; }
    .stat-card .label { font-size: 0.8rem; color: #999; margin-top: 6px; }

    /* ── FILTRES ── */
    .filters {
      display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
      background: #fff; border-radius: 14px; padding: 18px 20px;
      margin-bottom: 24px; box-shadow: 0 4px 20px rgba(58,34,24,0.08);
    }
    .filters input, .filters select {
      padding: 9px 14px; border: 1.5px solid #e0d8cc; border-radius: 8px;
      font-size: 0.88rem; outline: none; background: #faf6f0;
      transition: border-color .2s;
    }
    .filters input:focus, .filters select:focus { border-color: #8B1A1A; }
    .filters input { flex: 1; min-width: 180px; }
    .btn-filter {
      padding: 9px 20px; background: #8B1A1A; color: #fff;
      border: none; border-radius: 8px; cursor: pointer; font-size: 0.88rem;
      transition: background .2s;
    }
    .btn-filter:hover { background: #B94040; }

    /* ── TABLE ── */
    .table-wrap {
      background: #fff; border-radius: 14px; overflow: hidden;
      box-shadow: 0 4px 20px rgba(58,34,24,0.08);
    }
    .table-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 18px 24px; border-bottom: 1px solid #f0ece6;
    }
    .table-header h2 { font-size: 1rem; font-weight: 600; }
    .count-badge {
      background: #fce8e8; color: #8B1A1A; font-size: 0.78rem;
      padding: 3px 10px; border-radius: 20px; font-weight: 600;
    }
    table { width: 100%; border-collapse: collapse; }
    thead th {
      background: #faf6f0; padding: 12px 16px; text-align: left;
      font-size: 0.75rem; letter-spacing: 0.06em; text-transform: uppercase;
      color: #999; font-weight: 600; border-bottom: 1px solid #f0ece6;
    }
    tbody tr { border-bottom: 1px solid #f7f4f0; transition: background .15s; }
    tbody tr:hover { background: #fdfaf7; }
    tbody td { padding: 14px 16px; font-size: 0.88rem; color: #2d2417; }
    .tel {
      font-family: monospace; font-size: 0.92rem;
      background: #f0ece6; padding: 4px 10px; border-radius: 6px;
      color: #3A2218; font-weight: 600; white-space: nowrap;
    }
    .badge {
      display: inline-block; padding: 3px 10px; border-radius: 20px;
      font-size: 0.75rem; font-weight: 600;
    }
    .badge-f { background: #fce8f3; color: #8B1A4A; }
    .badge-m { background: #e8eefce8; color: #1A3A8B; }
    .date-small { font-size: 0.78rem; color: #aaa; }

    /* ── EXPORT ── */
    .btn-export {
      padding: 9px 18px; background: #3A2218; color: #fff;
      border: none; border-radius: 8px; cursor: pointer; font-size: 0.82rem;
      transition: background .2s; text-decoration: none; display: inline-block;
    }
    .btn-export:hover { background: #5a3a28; }

    .empty { padding: 48px; text-align: center; color: #bbb; font-size: 0.9rem; }
  </style>
</head>
<body>

<?php if (!$connecte): ?>
<!-- ── PAGE DE CONNEXION ── -->
<div class="login-wrap">
  <div class="login-card">
    <h1>♡ Vie à deux</h1>
    <p>Panneau d'administration — accès réservé</p>
    <?php if ($est_bloque): ?>
      <div class="erreur">
        🔒 Trop de tentatives échouées. Réessayez dans <strong id="countdown"><?= gmdate('i:s', $temps_restant) ?></strong>.
      </div>
    <?php elseif (!empty($erreur_login)): ?>
      <div class="erreur">
        Identifiants incorrects.
        <?php if (isset($tentatives_restantes) && $tentatives_restantes > 0): ?>
          <?= $tentatives_restantes ?> tentative<?= $tentatives_restantes > 1 ? 's' : '' ?> restante<?= $tentatives_restantes > 1 ? 's' : '' ?>.
        <?php endif; ?>
      </div>
    <?php endif; ?>
    <form method="POST">
      <input type="text"     name="user" placeholder="Identifiant" required/>
      <input type="password" name="pass" placeholder="Mot de passe" required/>
      <?php if ($est_bloque): ?>
    <script>
    let restant = <?= $temps_restant ?>;
    function majCompteur() {
      if (restant <= 0) { location.reload(); return; }
      const m = String(Math.floor(restant / 60)).padStart(2,'0');
      const s = String(restant % 60).padStart(2,'0');
      const el = document.getElementById('countdown');
      if (el) el.textContent = m + ':' + s;
      restant--;
      setTimeout(majCompteur, 1000);
    }
    majCompteur();
    </script>
    <?php endif; ?>
    <button type="submit" name="login" class="btn-login" <?= $est_bloque ? 'disabled style="opacity:0.5;cursor:not-allowed"' : '' ?>>Se connecter</button>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ── INTERFACE ADMIN ── -->
<nav class="sidebar">
  <div class="logo">♡ Vie à deux<br><small style="font-size:.7rem;opacity:.6;font-weight:400">Administration</small></div>
  <a href="index.php" class="active">📋 Inscriptions</a>
  <a href="index.php?ordre=age">👥 Par âge</a>
  <a href="index.php?ordre=ville">📍 Par ville</a>
  <a href="?logout=1" class="logout" style="color:#f0a0a0;">🔓 Déconnexion</a>
</nav>

<main class="main">

  <!-- Stats -->
  <div class="stats">
    <div class="stat-card">
      <div class="num"><?= $total ?></div>
      <div class="label">Total inscrits</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $femmes ?></div>
      <div class="label">Femmes</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $hommes ?></div>
      <div class="label">Hommes</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $semaine ?></div>
      <div class="label">Cette semaine</div>
    </div>
    <div class="stat-card" style="border-left:3px solid #8B1A1A;">
      <div class="num" style="color:<?= $signalements > 0 ? '#8B1A1A' : '#4CAF50' ?>"><?= $signalements ?></div>
      <div class="label">Signalements en attente</div>
    </div>
  </div>

  <!-- Filtres -->
  <form class="filters" method="GET">
    <input type="text" name="q" placeholder="🔍  Rechercher par ville ou téléphone…"
           value="<?= htmlspecialchars($search) ?>"/>
    <select name="genre">
      <option value="">Tous les genres</option>
      <option value="Une femme"<?= $filtre_genre==='Une femme'?' selected':''?>>Femmes</option>
      <option value="Un homme"<?= $filtre_genre==='Un homme'?' selected':''?>>Hommes</option>
    </select>
    <select name="ordre">
      <option value="date_inscription"<?= $ordre==='date_inscription'?' selected':''?>>Plus récents</option>
      <option value="age"<?= $ordre==='age'?' selected':''?>>Par âge</option>
      <option value="ville"<?= $ordre==='ville'?' selected':''?>>Par ville</option>
    </select>
    <button type="submit" class="btn-filter">Filtrer</button>
    <a href="export.php?<?= http_build_query(['q'=>$search,'genre'=>$filtre_genre]) ?>"
       class="btn-export">⬇ Exporter CSV</a>
  </form>

  <!-- Tableau -->
  <div class="table-wrap">
    <div class="table-header">
      <h2>Liste des inscriptions</h2>
      <span class="count-badge"><?= count($inscriptions) ?> résultat<?= count($inscriptions)>1?'s':''?></span>
    </div>
    <?php if (empty($inscriptions)): ?>
      <div class="empty">Aucune inscription trouvée pour ces critères.</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Prénom & Nom</th>
          <th>Genre</th>
          <th>Âge</th>
          <th>Ville</th>
          <th>Téléphone</th>
          <th>Recherche</th>
          <th>Date inscription</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($inscriptions as $r): ?>
        <tr class="<?= $r['actif'] ? '' : 'inactif' ?>">
          <td style="color:#ccc"><?= $r['id'] ?></td>
          <td style="font-weight:500"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
          <td>
            <span class="badge <?= $r['je_suis']==='Une femme'?'badge-f':'badge-m' ?>">
              <?= $r['je_suis'] ?>
            </span>
          </td>
          <td><?= $r['age'] ?> ans</td>
          <td><?= htmlspecialchars($r['ville']) ?></td>
          <td><span class="tel"><?= htmlspecialchars($r['telephone']) ?></span></td>
          <td><?= htmlspecialchars($r['je_cherche']) ?></td>
          <td class="date-small"><?= date('d/m/Y H:i', strtotime($r['date_inscription'])) ?></td>
          <td>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
              <?php if ($r['actif']): ?>
                <a href="?desactiver=<?= $r['id'] ?>"
                   onclick="return confirm('Désactiver <?= htmlspecialchars($r['prenom']) ?> ?')"
                   style="padding:5px 10px;background:#fff3cd;color:#856404;border-radius:6px;font-size:0.75rem;text-decoration:none;border:1px solid #ffc107;">
                  ⏸ Désactiver
                </a>
              <?php else: ?>
                <a href="?reactiver=<?= $r['id'] ?>"
                   onclick="return confirm('Réactiver <?= htmlspecialchars($r['prenom']) ?> ?')"
                   style="padding:5px 10px;background:#d4edda;color:#155724;border-radius:6px;font-size:0.75rem;text-decoration:none;border:1px solid #28a745;">
                  ▶ Réactiver
                </a>
              <?php endif; ?>
              <a href="?supprimer=<?= $r['id'] ?>"
                 onclick="return confirm('⚠️ Supprimer définitivement <?= htmlspecialchars($r['prenom']) ?> ? Cette action est irréversible !')"
                 style="padding:5px 10px;background:#fce8e8;color:#8B1A1A;border-radius:6px;font-size:0.75rem;text-decoration:none;border:1px solid rgba(139,26,26,0.3);">
                🗑 Supprimer
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

</main>
<?php endif; ?>
</body>
</html>
