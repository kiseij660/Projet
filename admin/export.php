<?php
// ============================================================
//  admin/export.php — Export CSV de tous les inscrits
//  Accès : http://localhost/vieadeux/admin/export.php
// ============================================================
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}

$pdo = new PDO('mysql:host=localhost;dbname=vieadeux;charset=utf8', 'root', '');

$search = htmlspecialchars(trim($_GET['q'] ?? ''));
$genre  = $_GET['genre'] ?? '';

$sql = "SELECT id, je_suis, je_cherche, ville, age, telephone, date_inscription FROM inscriptions WHERE actif=1";
$params = [];
if ($search) { $sql .= " AND (ville LIKE :q OR telephone LIKE :q)"; $params[':q'] = "%$search%"; }
if ($genre)  { $sql .= " AND je_suis = :genre"; $params[':genre'] = $genre; }
$sql .= " ORDER BY date_inscription DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Envoi du fichier CSV
$filename = 'inscriptions_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 pour Excel

fputcsv($out, ['ID', 'Genre', 'Recherche', 'Ville', 'Âge', 'Téléphone', 'Date inscription'], ';');
foreach ($rows as $r) {
    fputcsv($out, [
        $r['id'],
        $r['je_suis'],
        $r['je_cherche'],
        $r['ville'],
        $r['age'],
        $r['telephone'],
        $r['date_inscription'],
    ], ';');
}
fclose($out);
exit;
?>
