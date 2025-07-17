<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent_biblio') {
    header("Location: login.php");
    exit;
}

// Valeurs par défaut pour les filtres
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$recherche = $_GET['recherche'] ?? '';

// Construction de la requête avec filtres
$sql = "SELECT acces_service.*, etudiants.nom, etudiants.prenom, etudiants.matricule 
        FROM acces_service 
        JOIN etudiants ON acces_service.etudiant_id = etudiants.id 
        WHERE service = 'bibliothèque'";

$params = [];
$types = "";

if ($date_debut) {
    $sql .= " AND date_acces >= ?";
    $params[] = $date_debut . " 00:00:00";
    $types .= "s";
}

if ($date_fin) {
    $sql .= " AND date_acces <= ?";
    $params[] = $date_fin . " 23:59:59";
    $types .= "s";
}

if ($recherche) {
    $sql .= " AND (etudiants.nom LIKE ? OR etudiants.prenom LIKE ? OR etudiants.matricule LIKE ?)";
    $like_recherche = "%" . $recherche . "%";
    $params[] = $like_recherche;
    $params[] = $like_recherche;
    $params[] = $like_recherche;
    $types .= "sss";
}

$sql .= " ORDER BY date_acces DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des accès bibliothèque</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        h2 { text-align: center; }
        form { margin-bottom: 20px; background: white; padding: 15px; border-radius: 5px; box-shadow: 0 0 5px #ccc; max-width: 600px; margin-left: auto; margin-right: auto; }
        label { margin-right: 10px; }
        input[type="date"], input[type="text"] { padding: 6px; margin-right: 15px; }
        button { padding: 6px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 0 5px #ccc; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
        th { background: #007bff; color: white; }
    </style>
</head>
<body>

<h2>📊 Historique des accès bibliothèque</h2>

<form method="get" action="">
    <label>Date début : <input type="date" name="date_debut" value="<?= htmlspecialchars($date_debut) ?>"></label>
    <label>Date fin : <input type="date" name="date_fin" value="<?= htmlspecialchars($date_fin) ?>"></label>
    <label>Recherche : <input type="text" name="recherche" placeholder="Nom, prénom ou matricule" value="<?= htmlspecialchars($recherche) ?>"></label>
    <button type="submit">Filtrer</button>
</form>

<table>
    <tr>
        <th>Nom</th>
        <th>Matricule</th>
        <th>Date & Heure</th>
    </tr>
    <?php if ($result->num_rows === 0): ?>
    <tr><td colspan="3">Aucun accès trouvé.</td></tr>
    <?php else: ?>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= htmlspecialchars($row['prenom'] . ' ' . $row['nom']) ?></td>
        <td><?= htmlspecialchars($row['matricule']) ?></td>
        <td><?= date('d/m/Y H:i', strtotime($row['date_acces'])) ?></td>
    </tr>
    <?php endwhile; ?>
    <?php endif; ?>
</table>

</body>
</html>
