<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    header('Location: login.php');
    exit();
}

$id_etudiant = $_SESSION['id'];

// On récupère tous les cours auxquels l'étudiant est inscrit
$sql = "
    SELECT c.id, c.nom AS nom_cours, c.statut
    FROM cours c
    JOIN inscriptions i ON c.id = i.id_cours
    WHERE i.id_etudiant = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_etudiant);
$stmt->execute();
$result = $stmt->get_result();

$stats = [];

while ($row = $result->fetch_assoc()) {
    $cours_id = $row['id'];

    // Nombre total de séances pour ce cours
    $stmt2 = $conn->prepare("SELECT COUNT(DISTINCT DATE(date_pointage)) FROM presences WHERE id_cours = ?");
    $stmt2->bind_param("i", $cours_id);
    $stmt2->execute();
    $stmt2->bind_result($total_seances);
    $stmt2->fetch();
    $stmt2->close();

    // Nombre de présences de cet étudiant pour ce cours
    $stmt3 = $conn->prepare("SELECT COUNT(*) FROM presences WHERE id_cours = ? AND id_etudiant = ?");
    $stmt3->bind_param("ii", $cours_id, $id_etudiant);
    $stmt3->execute();
    $stmt3->bind_result($presences);
    $stmt3->fetch();
    $stmt3->close();

    $taux = ($total_seances > 0) ? round(($presences / $total_seances) * 100, 2) : 0;

    $stats[] = [
        'cours' => $row['nom_cours'],
        'statut' => $row['statut'],
        'total_seances' => $total_seances,
        'presences' => $presences,
        'taux' => $taux
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Statistiques</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 10px; max-width: 900px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #0066cc; color: white; }
        .etat { padding: 5px 10px; border-radius: 5px; color: white; font-weight: bold; }
        .en_cours { background: #28a745; }
        .cloture { background: #dc3545; }
        .non_intro { background: #ffc107; color: #333; }
    </style>
</head>
<body>

<div class="box">
    <h2>Mes statistiques de participation</h2>
    <table>
        <tr>
            <th>Cours</th>
            <th>Statut</th>
            <th>Présences</th>
            <th>Séances totales</th>
            <th>Taux</th>
        </tr>
        <?php foreach ($stats as $stat) { 
            $class = $stat['statut'] === 'en cours' ? 'en_cours' : ($stat['statut'] === 'clôturé' ? 'cloture' : 'non_intro');
        ?>
            <tr>
                <td><?= htmlspecialchars($stat['cours']) ?></td>
                <td><span class="etat <?= $class ?>"><?= ucfirst($stat['statut']) ?></span></td>
                <td><?= $stat['presences'] ?></td>
                <td><?= $stat['total_seances'] ?></td>
                <td><?= $stat['taux'] ?>%</td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
