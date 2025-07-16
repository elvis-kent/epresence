<?php
require_once "config.php";

// ⚠️ Simule l'utilisateur connecté : ici, "superadmin" ou "autre"
$utilisateur_actuel = "superadmin"; // à remplacer par une vraie session plus tard

// Vérifie l'ID dans l'URL
if (!isset($_GET['id'])) {
    echo "ID manquant.";
    exit();
}

$id = $_GET['id'];

// Récupère les données de l’étudiant
$stmt = $conn->prepare("SELECT * FROM etudiants WHERE id = ?");
$stmt->execute([$id]);
$etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$etudiant) {
    echo "Étudiant introuvable.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte Étudiante</title>
    <style>
        .carte {
            width: 400px;
            padding: 20px;
            border: 2px solid #333;
            border-radius: 10px;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .carte img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
        }
        .qr {
            margin-top: 15px;
        }
        .telecharger {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="carte">
        <h2>Carte Étudiante</h2>
        <img src="<?= $etudiant['photo'] ?>" alt="Photo étudiant"><br><br>
        <strong><?= htmlspecialchars($etudiant['nom']) ?></strong><br>
        Matricule : <?= htmlspecialchars($etudiant['matricule']) ?><br>
        <?= htmlspecialchars($etudiant['faculte']) ?> /
        <?= htmlspecialchars($etudiant['departement']) ?><br>
        <?= htmlspecialchars($etudiant['filiere']) ?> - <?= htmlspecialchars($etudiant['promotion']) ?><br>

        <div class="qr">
            <img src="<?= $etudiant['qr_code'] ?>" alt="QR Code">
        </div>

        <?php if ($utilisateur_actuel === "superadmin"): ?>
        <div class="telecharger">
            <button onclick="window.print()">🖨️ Imprimer / Télécharger</button>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
