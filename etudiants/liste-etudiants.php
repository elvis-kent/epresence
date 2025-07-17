<?php
session_start();
require_once 'config.php';

// Vérification de rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header("Location: login.php");
    exit;
}

// Rechercher
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Requête avec filtre facultatif
$sql = "SELECT * FROM etudiants WHERE departement = ? ";
$params = [$_SESSION['departement']];

if (!empty($search)) {
    $sql .= "AND (nom LIKE ? OR matricule LIKE ?) ";
    $likeSearch = "%" . $search . "%";
    array_push($params, $likeSearch, $likeSearch);
}

$sql .= "ORDER BY promotion, nom";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Étudiants</title>
    <style>
        body { font-family: Arial; background: #f2f2f2; padding: 30px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .search-form input[type="text"] {
            padding: 8px; width: 300px; margin-bottom: 10px;
        }
        .actions a {
            padding: 5px 10px; text-decoration: none;
            background: #2c3e50; color: white; border-radius: 4px;
            margin: 0 3px;
        }
        .actions a:hover { background: #1a242f; }
    </style>
</head>
<body>

<h2>📋 Liste des Étudiants - <?= htmlspecialchars($_SESSION['departement']) ?></h2>

<form class="search-form" method="GET" style="text-align:center;">
    <input type="text" name="search" placeholder="Rechercher par nom ou matricule" value="<?= htmlspecialchars($search) ?>">
    <input type="submit" value="🔍 Rechercher">
</form>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Matricule</th>
            <th>Nom</th>
            <th>Promotion</th>
            <th>QR Code</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php $i = 1; while ($etudiant = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $i++ ?></td>
            <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
            <td><?= htmlspecialchars($etudiant['nom']) ?></td>
            <td><?= htmlspecialchars($etudiant['promotion']) ?></td>
            <td><img src="phpqrcode/temp/<?= $etudiant['qr_code'] ?>" width="60"></td>
            <td class="actions">
                <a href="modifier_etudiant.php?id=<?= $etudiant['id'] ?>">🖊️ Modifier</a>
                <a href="supprimer_etudiant.php?id=<?= $etudiant['id'] ?>" onclick="return confirm('Confirmer la suppression ?')">❌ Supprimer</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
