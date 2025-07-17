<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

$id_prof = $_SESSION['id'];

// Récupération des cours du prof
$cours = $conn->query("
    SELECT c.id, c.nom AS nom_cours
    FROM cours c
    WHERE c.id_professeur = $id_prof
");

$filtre_date = $_GET['date'] ?? '';
$search = $_GET['search'] ?? '';
$cours_id = $_GET['cours_id'] ?? '';

// Requête de base
$sql = "
    SELECT p.*, e.nom, e.postnom, e.prenom, e.matricule, c.nom AS cours
    FROM presences p
    JOIN etudiants e ON p.id_etudiant = e.id
    JOIN cours c ON p.id_cours = c.id
    WHERE c.id_professeur = ?
";

$params = [$id_prof];
$types = 'i';

if (!empty($cours_id)) {
    $sql .= " AND c.id = ?";
    $params[] = $cours_id;
    $types .= 'i';
}

if (!empty($filtre_date)) {
    $sql .= " AND DATE(p.date_pointage) = ?";
    $params[] = $filtre_date;
    $types .= 's';
}

if (!empty($search)) {
    $sql .= " AND (e.nom LIKE ? OR e.postnom LIKE ? OR e.prenom LIKE ? OR e.matricule LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= 'ssss';
}

$sql .= " ORDER BY p.date_pointage DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des présences</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 8px; max-width: 1000px; margin: auto; }
        input, select {
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: calc(33% - 10px);
        }
        button {
            padding: 8px 16px;
            margin-top: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #343a40; color: white; }
    </style>
</head>
<body>

<div class="box">
    <h2>Liste des présences</h2>

    <form method="GET">
        <select name="cours_id">
            <option value="">Tous les cours</option>
            <?php while ($row = $cours->fetch_assoc()) { ?>
                <option value="<?= $row['id'] ?>" <?= ($cours_id == $row['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['nom_cours']) ?>
                </option>
            <?php } ?>
        </select>

        <input type="date" name="date" value="<?= htmlspecialchars($filtre_date) ?>" />
        <input type="text" name="search" placeholder="Recherche par nom ou matricule" value="<?= htmlspecialchars($search) ?>" />
        <button type="submit">Filtrer</button>
    </form>

    <table>
        <tr>
            <th>Nom complet</th>
            <th>Matricule</th>
            <th>Cours</th>
            <th>Date</th>
            <th>Heure</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['nom'] . ' ' . $row['postnom'] . ' ' . $row['prenom'] ?></td>
                <td><?= $row['matricule'] ?></td>
                <td><?= $row['cours'] ?></td>
                <td><?= date('d/m/Y', strtotime($row['date_pointage'])) ?></td>
                <td><?= date('H:i', strtotime($row['date_pointage'])) ?></td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
