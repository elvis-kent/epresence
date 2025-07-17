<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit();
}

$id_prof = $_SESSION['id'];

// Récupération des cours du prof
$cours = $conn->query("SELECT id, nom FROM cours WHERE id_professeur = $id_prof");

$cours_id = $_GET['cours_id'] ?? '';
$statistiques = [];

if (!empty($cours_id)) {
    // Nombre total de séances pour ce cours
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT DATE(date_pointage)) FROM presences WHERE id_cours = ?");
    $stmt->bind_param("i", $cours_id);
    $stmt->execute();
    $stmt->bind_result($total_seances);
    $stmt->fetch();
    $stmt->close();

    if ($total_seances > 0) {
        // Liste des étudiants + nombre de présences
        $sql = "
            SELECT e.nom, e.postnom, e.prenom, e.matricule, COUNT(p.id) AS nb_presences
            FROM presences p
            JOIN etudiants e ON p.id_etudiant = e.id
            WHERE p.id_cours = ?
            GROUP BY p.id_etudiant
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cours_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $taux = ($row['nb_presences'] / $total_seances) * 100;
            $row['taux'] = round($taux, 2);
            $statistiques[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques de participation</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .box { background: white; padding: 20px; border-radius: 10px; max-width: 900px; margin: auto; }
        select, button {
            padding: 8px; margin: 10px 0; width: 100%;
            border-radius: 5px; border: 1px solid #ccc;
        }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #333; color: white; }
        .taux-bar {
            background: #4caf50;
            height: 20px;
            border-radius: 3px;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>Statistiques de participation</h2>

    <form method="GET">
        <label>Choisir un cours :</label>
        <select name="cours_id" onchange="this.form.submit()">
            <option value="">-- Choisir un cours --</option>
            <?php while ($row = $cours->fetch_assoc()) { ?>
                <option value="<?= $row['id'] ?>" <?= ($cours_id == $row['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($row['nom']) ?>
                </option>
            <?php } ?>
        </select>
    </form>

    <?php if (!empty($statistiques)) { ?>
        <table>
            <tr>
                <th>Nom complet</th>
                <th>Matricule</th>
                <th>Présences</th>
                <th>Taux</th>
            </tr>
            <?php foreach ($statistiques as $stat) { ?>
                <tr>
                    <td><?= $stat['nom'] . ' ' . $stat['postnom'] . ' ' . $stat['prenom'] ?></td>
                    <td><?= $stat['matricule'] ?></td>
                    <td><?= $stat['nb_presences'] ?></td>
                    <td>
                        <?= $stat['taux'] ?>%
                        <div class="taux-bar" style="width: <?= $stat['taux'] ?>%"></div>
                    </td>
                </tr>
            <?php } ?>
        </table>
    <?php } elseif (!empty($cours_id)) { ?>
        <p>Aucune donnée de présence pour ce cours pour le moment.</p>
    <?php } ?>
</div>

</body>
</html>
