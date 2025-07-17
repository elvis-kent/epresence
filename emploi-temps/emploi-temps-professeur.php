<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header("Location: login.php");
    exit;
}

$prof_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT * FROM horaires WHERE professeur_id = ? ORDER BY 
    FIELD(jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'),
    heure_debut");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes cours - Emploi du temps</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #eef0f5; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 0 5px #aaa; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #f5f5f5; }
    </style>
</head>
<body>

<h2>📚 Mes créneaux de cours</h2>

<table>
    <thead>
        <tr>
            <th>Jour</th>
            <th>Heure</th>
            <th>Cours</th>
            <th>Salle</th>
            <th>Promotion</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()):
            // Récupérer le nom de la promotion
            $promo_query = $conn->query("SELECT nom FROM promotions WHERE id = " . $row['promotion_id']);
            $promo = $promo_query->fetch_assoc();
        ?>
            <tr>
                <td><?= htmlspecialchars($row['jour_semaine']) ?></td>
                <td><?= substr($row['heure_debut'], 0, 5) ?> - <?= substr($row['heure_fin'], 0, 5) ?></td>
                <td><?= htmlspecialchars($row['nom_cours']) ?></td>
                <td><?= htmlspecialchars($row['salle']) ?></td>
                <td><?= htmlspecialchars($promo['nom'] ?? 'N/A') ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
