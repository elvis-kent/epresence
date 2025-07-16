<?php
require_once "config.php";

if (!isset($_GET['matricule'])) {
    echo "Matricule manquant.";
    exit();
}

$matricule = $_GET['matricule'];

// Cherche l'étudiant
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE matricule = ?");
$stmt->execute([$matricule]);
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$etudiant) {
    echo "Aucun étudiant trouvé.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Étudiant</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #eef;
      padding: 30px;
    }
    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 500px;
      margin: auto;
      text-align: center;
      box-shadow: 0 0 10px #ccc;
    }
    img {
      width: 100px;
      height: 100px;
      border-radius: 50%;
    }
    table {
      margin-top: 20px;
      width: 100%;
    }
    th, td {
      border-bottom: 1px solid #ccc;
      padding: 10px;
    }
    .statut {
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="card">
  <h2>Bienvenue <?= htmlspecialchars($etudiant['nom']) ?></h2>
  <img src="<?= $etudiant['photo'] ?>" alt="Photo"><br><br>
  <p><strong>Matricule :</strong> <?= $etudiant['matricule'] ?></p>
  <p><strong>Filière :</strong> <?= $etudiant['filiere'] ?> / <?= $etudiant['promotion'] ?></p>

  <a href="carte.php?id=<?= $etudiant['id'] ?>" target="_blank">🎫 Voir ma carte étudiante</a>

  <h3>Mes cours</h3>
  <table>
    <tr>
      <th>Cours</th>
      <th>Statut</th>
      <th>Participation</th>
    </tr>

    <?php
    // Récupération des cours associés
    $req = $conn->prepare("SELECT * FROM cours WHERE filiere = ? AND promotion = ?");
    $req->execute([$etudiant['filiere'], $etudiant['promotion']]);
    while ($cours = $req->fetch(PDO::FETCH_ASSOC)) {
        // Statut fictif (à adapter)
        $statut = $cours['statut'] ?? 'non introduit'; // colonne à prévoir dans la table "cours"
        $pourcentage = rand(60, 100); // à calculer réellement plus tard

        echo "<tr>";
        echo "<td>" . htmlspecialchars($cours['nom']) . "</td>";
        echo "<td class='statut'>" . strtoupper($statut) . "</td>";
        echo "<td>$pourcentage%</td>";
        echo "</tr>";
    }
    ?>

  </table>
</div>

</body>
</html>
