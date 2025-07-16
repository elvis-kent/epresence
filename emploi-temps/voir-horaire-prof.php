<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['prof_id'])) {
    header('Location: prof_login.php');
    exit;
}

$prof_id = $_SESSION['prof_id'];

$stmt = $conn->prepare("
    SELECT h.jour_semaine, h.heure_debut, h.heure_fin, h.salle, c.nom AS nom_cours, p.nom AS promotion
    FROM horaires h
    JOIN cours c ON h.cours_id = c.id
    JOIN promotions p ON h.promotion_id = p.id
    WHERE c.prof_id = ?
    ORDER BY FIELD(h.jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'), h.heure_debut
");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$horaires = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon emploi du temps</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #28a745; color: white; }
    </style>
</head>
<body>

<h2>Mes horaires de cours</h2>

<?php if ($horaires->num_rows > 0): ?>
    <table>
        <tr>
            <th>Jour</th>
            <th>Heure Début</th>
            <th>Heure Fin</th>
            <th>Salle</th>
            <th>Cours</th>
            <th>Promotion</th>
        </tr>
        <?php while ($row = $horaires->fetch_assoc()): ?>
            <tr>
                <td><?= $row['jour_semaine'] ?></td>
                <td><?= $row['heure_debut'] ?></td>
                <td><?= $row['heure_fin'] ?></td>
                <td><?= $row['salle'] ?></td>
                <td><?= $row['nom_cours'] ?></td>
                <td><?= $row['promotion'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Vous n'avez pas encore d’horaires attribués.</p>
<?php endif; ?>

</body>
</html>
