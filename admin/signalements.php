<?php
session_start();
if (empty($_SESSION['admin'])) { header('Location: index.php'); exit; }

$pdo = new PDO('mysql:host=localhost;dbname=vieadeux;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Marquer comme traité
if (isset($_GET['desactiver_sig'])) {
    $pdo->prepare("UPDATE inscriptions SET actif=0 WHERE id=:id")
        ->execute([':id' => intval($_GET['desactiver_sig'])]);
    header('Location: signalements.php');
    exit;
}

if (isset($_GET['traiter'])) {
    $pdo->prepare("UPDATE signalements SET traite=1 WHERE id=:id")->execute([':id' => intval($_GET['traiter'])]);
    header('Location: signalements.php');
    exit;
}

$signalements = $pdo->query("
    SELECT s.*, 
        i1.prenom AS prenom_signaleur, i1.nom AS nom_signaleur, i1.telephone AS tel_signaleur, i1.ville AS ville_signaleur,
        i2.prenom AS prenom_signale,  i2.nom AS nom_signale,  i2.telephone AS tel_signale,  i2.ville AS ville_signale,
        i2.email AS email_signale, i2.je_suis AS genre_signale,
        TIMESTAMPDIFF(YEAR, i2.date_naissance, CURDATE()) AS age_signale
    FROM signalements s
    JOIN inscriptions i1 ON s.id_signaleur = i1.id
    JOIN inscriptions i2 ON s.id_signale   = i2.id
    ORDER BY s.traite ASC, s.date_signalement DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/>
  <title>Signalements — Admin Vie à deux</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: system-ui, sans-serif; background: #f5f3ef; color: #2d2417; }
    .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 220px; background: #3A2218; color: rgba(255,255,255,0.8); padding: 32px 20px; display: flex; flex-direction: column; gap: 8px; }
    .sidebar .logo { font-size: 1.2rem; color: #fff; margin-bottom: 24px; font-weight: 600; }
    .sidebar a { display: block; padding: 10px 14px; border-radius: 8px; color: rgba(255,255,255,0.7); text-decoration: none; font-size: 0.88rem; transition: background .2s; }
    .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.12); color: #fff; }
    .sidebar .logout { margin-top: auto; }
    .main { margin-left: 220px; padding: 32px; }
    h1 { font-size: 1.3rem; font-weight: 600; margin-bottom: 24px; color: #3A2218; }
    .card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 20px rgba(58,34,24,0.08); margin-bottom: 16px; }
    .sig-header { display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #f0ece6; }
    .sig-title { font-weight: 600; font-size: 0.95rem; }
    .sig-date { font-size: 0.78rem; color: #aaa; }
    .sig-body { padding: 16px 24px; }
    .sig-row { display: flex; gap: 32px; margin-bottom: 10px; flex-wrap: wrap; }
    .sig-item { font-size: 0.85rem; }
    .sig-item span { color: #999; font-size: 0.78rem; display: block; margin-bottom: 2px; }
    .raison { display: inline-block; background: #fce8e8; color: #8B1A1A; padding: 3px 12px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
    .desc { font-size: 0.85rem; color: #5A4A3A; background: #faf6f0; border-radius: 8px; padding: 10px 14px; margin-top: 10px; }
    .btn-traiter { padding: 8px 18px; background: #4CAF50; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 0.82rem; transition: background .2s; text-decoration: none; display: inline-block; }
    .btn-traiter:hover { background: #388E3C; }
    .traite { opacity: 0.5; }
    .badge-traite { background: #e8f5e9; color: #2E7D32; padding: 3px 12px; border-radius: 20px; font-size: 0.75rem; }
    .badge-attente { background: #fce8e8; color: #8B1A1A; padding: 3px 12px; border-radius: 20px; font-size: 0.75rem; }
    .empty { text-align: center; padding: 60px; color: #bbb; }
  </style>
</head>
<body>
<nav class="sidebar">
  <div class="logo">♡ Vie à deux<br><small style="font-size:.7rem;opacity:.6;font-weight:400">Administration</small></div>
  <a href="index.php">📋 Inscriptions</a>
  <a href="signalements.php" class="active">🚨 Signalements</a>
  <a href="?logout=1" class="logout" style="color:#f0a0a0;">🔓 Déconnexion</a>
</nav>

<main class="main">
  <h1>🚨 Signalements (<?= count($signalements) ?>)</h1>

  <?php if (empty($signalements)): ?>
    <div class="card"><div class="empty">✅ Aucun signalement pour l'instant.</div></div>
  <?php else: ?>
    <?php foreach ($signalements as $s): ?>
      <div class="card <?= $s['traite'] ? 'traite' : '' ?>">
        <div class="sig-header">
          <div>
            <div class="sig-title">
              <?= htmlspecialchars($s['prenom_signaleur'].' '.$s['nom_signaleur']) ?>
              → signale →
              <?= htmlspecialchars($s['prenom_signale'].' '.$s['nom_signale']) ?>
            </div>
            <div class="sig-date"><?= date('d/m/Y H:i', strtotime($s['date_signalement'])) ?></div>
          </div>
          <?php if ($s['traite']): ?>
            <span class="badge-traite">✓ Traité</span>
          <?php else: ?>
            <span class="badge-attente">⏳ En attente</span>
          <?php endif; ?>
        </div>
        <div class="sig-body">
          <!-- Profil signalé -->
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
            <div style="background:#fce8e8;border-radius:10px;padding:14px 16px;">
              <div style="font-size:0.75rem;color:#8B1A1A;font-weight:600;margin-bottom:8px;text-transform:uppercase;">🚨 Profil signalé</div>
              <div style="font-size:0.92rem;font-weight:600;color:#3A2218;"><?= htmlspecialchars($s['prenom_signale'].' '.$s['nom_signale']) ?></div>
              <div style="font-size:0.8rem;color:#7A6E68;margin-top:3px;"><?= htmlspecialchars($s['genre_signale']) ?> · <?= $s['age_signale'] ?> ans · <?= htmlspecialchars($s['ville_signale']) ?></div>
              <div style="font-size:0.8rem;color:#7A6E68;margin-top:2px;">📧 <?= htmlspecialchars($s['email_signale']) ?></div>
              <div style="font-size:0.8rem;color:#7A6E68;margin-top:2px;">📞 <?= htmlspecialchars($s['tel_signale']) ?></div>
              <div style="margin-top:10px;display:flex;gap:8px;">
                <a href="../index.php?search=<?= urlencode($s['tel_signale']) ?>" style="font-size:0.75rem;padding:5px 10px;background:#8B1A1A;color:#fff;border-radius:6px;text-decoration:none;">Voir dans admin</a>
                <a href="?desactiver_sig=<?= $s['id_signale'] ?>" onclick="return confirm('Désactiver ce membre ?')" style="font-size:0.75rem;padding:5px 10px;background:#fff3cd;color:#856404;border-radius:6px;text-decoration:none;border:1px solid #ffc107;">⏸ Désactiver</a>
              </div>
            </div>
            <div style="background:#f0f0f0;border-radius:10px;padding:14px 16px;">
              <div style="font-size:0.75rem;color:#666;font-weight:600;margin-bottom:8px;text-transform:uppercase;">👤 Signalé par</div>
              <div style="font-size:0.92rem;font-weight:600;color:#3A2218;"><?= htmlspecialchars($s['prenom_signaleur'].' '.$s['nom_signaleur']) ?></div>
              <div style="font-size:0.8rem;color:#7A6E68;margin-top:3px;">📍 <?= htmlspecialchars($s['ville_signaleur']) ?></div>
              <div style="font-size:0.8rem;color:#7A6E68;margin-top:2px;">📞 <?= htmlspecialchars($s['tel_signaleur']) ?></div>
            </div>
          </div>
          <div class="sig-row">
            <div class="sig-item"><span>Raison</span><span class="raison"><?= htmlspecialchars($s['raison']) ?></span></div>
          </div>
          <?php if ($s['description']): ?>
            <div class="desc"><?= htmlspecialchars($s['description']) ?></div>
          <?php endif; ?>
          <?php if (!$s['traite']): ?>
            <div style="margin-top:14px;">
              <a href="?traiter=<?= $s['id'] ?>" class="btn-traiter" onclick="return confirm('Marquer ce signalement comme traité ?')">✓ Marquer comme traité</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
</body>
</html>
