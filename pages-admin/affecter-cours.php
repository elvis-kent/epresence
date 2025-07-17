<?php
require_once('../includes/config.php');
session_start();

// Vérifie que l'admin de l'université est connecté
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header("Location: ../login.php");
    exit();
}

$message = "";

// Affectation d’un cours
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $professeur_id = intval($_POST['professeur_id']);
    $cours_id = intval($_POST['cours_id']);

    $stmt = $conn->prepare("UPDATE cours SET professeur_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $professeur_id, $cours_id);

    if ($stmt->execute()) {
        $message = "✅ Cours affecté avec succès.";
    } else {
        $message = "❌ Erreur lors de l'affectation : " . $stmt->error;
    }

    $stmt->close();
}

// Rafraîchir les listes après traitement
$professeurs = $conn->query("SELECT id, nom, postnom, prenom FROM professeurs");
$cours = $conn->query("SELECT id, nom_cours FROM cours WHERE professeur_id IS NULL");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Affecter un cours</title>
    <style>
        body {
            font-family: Arial;
            background: #e0f2f1;
            padding: 30px;
        }
        .container {
            max-width: 600px; margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            color: #00695c;
        }
        label {
            font-weight: bold;
        }
        select, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .message {
            margin-top: 15px;
            text-align: center;
            padding: 10px;
            color: white;
            border-radius: 5px;
        }
        .message.success {
            background-color: #2e7d32;
        }
        .message.error {
            background-color: #c62828;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Affecter un cours à un professeur</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, '✅') !== false ? 'success' : 'error' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label>Choisir le professeur :</label>
        <select name="professeur_id" required>
            <option value="">-- Sélectionner un professeur --</option>
            <?php while ($prof = $professeurs->fetch_assoc()): ?>
                <option value="<?= $prof['id'] ?>">
                    <?= htmlspecialchars($prof['nom'] . ' ' . $prof['postnom'] . ' ' . $prof['prenom']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Choisir le cours :</label>
        <select name="cours_id" required>
            <option value="">-- Sélectionner un cours --</option>
            <?php while ($c = $cours->fetch_assoc()): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom_cours']) ?></option>
            <?php endwhile; ?>
        </select>

        <button type="submit">✅ Affecter le cours</button>
    </form>
</div>

</body>
</html>
