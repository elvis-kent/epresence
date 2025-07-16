<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['prof_id'], $_SESSION['cours_id'])) {
    header('Location: prof_login.php');
    exit;
}

$cours_id = $_SESSION['cours_id'];
$date = $_GET['date'] ?? date('Y-m-d');
$search_nom = $_GET['search_nom'] ?? '';

// Préparer requête avec filtre
$sql = "
    SELECT e.nom, e.prenom, p.date_presence, p.heure_presence
    FROM presences p
    JOIN etudiants e ON p.etudiant_id = e.id
    WHERE p.cours_id = ? AND p.date_presence = ?
";

if ($search_nom !== '') {
    $sql .= " AND (e.nom LIKE ? OR e.prenom LIKE ?)";
}

$sql .= " ORDER BY p.heure_presence ASC";

$stmt = $conn->prepare($sql);

if ($search_nom !== '') {
    $like_nom = "%$search_nom%";
    $stmt->bind_param("isss", $cours_id, $date, $like_nom, $like_nom);
} else {
    $stmt->bind_param("is", $cours_id, $date);
}

$stmt->execute();
$result = $stmt->get_result();

// En-têtes HTTP pour forcer téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=presences_' . $date . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Nom', 'Prénom', 'Date présence', 'Heure présence']);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [$row['nom'], $row['prenom'], $row['date_presence'], $row['heure_presence']]);
}

fclose($output);
exit;
