<?php
session_start();
require_once 'config.php';

// Vérifier que prof est connecté
if (!isset($_SESSION['prof_id'], $_SESSION['cours_id'])) {
    header('Location: prof_login.php');
    exit;
}

$prof_id = $_SESSION['prof_id'];
$cours_id = $_SESSION['cours_id'];

$date = $_GET['date'] ?? date('Y-m-d');
$search_nom = $_GET['search_nom'] ?? '';

// Préparer la requête avec filtre dynamique
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

?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Liste des présences - ePresence</title>
<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f0f2f5; }
    h1 { color: #333; }
    form { margin-bottom: 20px; }
    input[type=date], input[type=text] {
        padding: 8px;
        font-size: 16px;
        margin-right: 10px;
    }
    button {
        padding: 8px 16px;
        font-size: 16px;
        cursor: pointer;
        margin-right: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    th {
        background-color: #007bff;
        color: white;
    }
    tr:hover {
        background-color: #f1f1f1;
    }
    a {
        color: #007bff;
        text-decoration: none;
    }
</style>
</head>
<body>

<h1>Liste des présences pour le <?= htmlspecialchars($date) ?></h1>

<!-- Formulaire de filtrage et export -->
<form method="get" action="">
    <label for="date">Date :</label>
    <input type="date" id="date" name="date" value="<?= htmlspecialchars($date) ?>" required />
    <label for="search_nom">Nom étudiant :</label>
    <input type="text" id="search_nom" name="search_nom" placeholder="Rechercher par nom" value="<?= htmlspecialchars($search_nom) ?>" />
    <button type="submit">Filtrer</button>

    <!-- Bouton Export CSV -->
    <button formaction="export_presences.php" formmethod="get" type="submit">
        Exporter en CSV
    </button>
</form>

<table>
    <thead>
        <tr>
            <th>Nom complet</th>
            <th>Date</th>
            <th>Heure de présence</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['prenom'] . ' ' . $row['nom']) ?></td>
                    <td><?= htmlspecialchars($row['date_presence']) ?></td>
                    <td><?= htmlspecialchars($row['heure_presence']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">Aucune présence trouvée avec ces critères.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="prof_dashboard.php">← Retour au tableau de bord</a></p>

</body>
</html>
