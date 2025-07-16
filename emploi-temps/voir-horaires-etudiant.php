<?php
require_once 'config.php';
$horaires = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = $_POST['matricule'];

    $stmt = $conn->prepare("
        SELECT promotion_id FROM etudiants WHERE matricule = ?
    ");
    $stmt->bind_param("s", $matricule);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $promotion_id = $row['promotion_id'];

        $stmt2 = $conn->prepare("
            SELECT h.jour_semaine, h.heure_debut, h.heure_fin, h.salle, c.nom AS nom_cours
            FROM horaires h
            JOIN cours c ON h.cours_id = c.id
            WHERE h.promotion_id = ?
            ORDER BY FIELD(h.jour_semaine, 'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'), h.heure_debut
        ");
        $stmt2->bind_param("i", $promotion_id);
        $stmt2->execute();
        $horaires = $stmt2->get_result();
    } else {
        $message = "Matricule introuvable.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - Étudiant</title>
    <style>
        body { font-family: Arial; background: #f7f7f7; padding: 20px; }
        input { padding: 8px; width: 250px; }
        button { padding: 8px 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background-color: #007bff; color: white; }
        .message { color: red; }
    </style>
</head>
<body>

<h2>Voir mon emploi du temps</h2>
<form method="post">
    <label>Matricule étudiant :</label>
    <input type="text" name="matricule" required />
    <button type="submit">Voir</button>
</form>

<?php if ($message): ?>
    <p class="message"><?= $message ?></p>
<?php endif; ?>

<?php if ($horaires && $horaires->num_rows > 0): ?>
    <h3>Résultats :</h3>
    <table>
        <tr>
            <th>Jour</th>
            <th>Heure Début</th>
            <th>Heure Fin</th>
            <th>Salle</th>
            <th>Cours</th>
        </tr>
        <?php while ($row = $horaires->fetch_assoc()): ?>
            <tr>
                <td><?= $row['jour_semaine'] ?></td>
                <td><?= $row['heure_debut'] ?></td>
                <td><?= $row['heure_fin'] ?></td>
                <td><?= $row['salle'] ?></td>
                <td><?= $row['nom_cours'] ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$message): ?>
    <p>Aucun horaire trouvé pour cette promotion.</p>
<?php endif; ?>

</body>
</html>
