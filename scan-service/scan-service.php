<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'agent') {
    header('Location: login_agent.php');
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_etudiant = trim($_POST['code_etudiant']); // L’ID ou matricule scanné

    // Vérifier si l’étudiant existe
    $stmt = $conn->prepare("SELECT id FROM etudiants WHERE id = ? OR matricule = ?");
    $stmt->bind_param("ss", $code_etudiant, $code_etudiant);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_etudiant);
        $stmt->fetch();
        $stmt->close();

        // Récupérer l’ID du service de l’agent
        $stmt2 = $conn->prepare("SELECT id_service FROM users WHERE id = ?");
        $stmt2->bind_param("i", $_SESSION['id']);
        $stmt2->execute();
        $stmt2->bind_result($id_service);
        $stmt2->fetch();
        $stmt2->close();

        // Vérifier si l’accès a déjà été enregistré aujourd’hui
        $stmt3 = $conn->prepare("SELECT id FROM acces_service WHERE id_etudiant = ? AND id_service = ? AND DATE(date_acces) = CURDATE()");
        $stmt3->bind_param("ii", $id_etudiant, $id_service);
        $stmt3->execute();
        $stmt3->store_result();

        if ($stmt3->num_rows > 0) {
            $message = "⚠️ Accès déjà enregistré pour aujourd'hui.";
        } else {
            // Enregistrement
            $stmt4 = $conn->prepare("INSERT INTO acces_service (id_etudiant, id_agent, id_service) VALUES (?, ?, ?)");
            $stmt4->bind_param("iii", $id_etudiant, $_SESSION['id'], $id_service);
            if ($stmt4->execute()) {
                $message = "✅ Accès accordé et enregistré avec succès.";
            } else {
                $message = "❌ Erreur lors de l'enregistrement.";
            }
            $stmt4->close();
        }

        $stmt3->close();
    } else {
        $message = "❌ Étudiant non reconnu.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scanner un étudiant</title>
    <style>
        body { font-family: Arial, background: #f7f7f7; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px #ccc; width: 400px; text-align: center; }
        input, button { padding: 10px; width: 100%; margin: 10px 0; }
        .msg { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="box">
    <h2>📷 Scanner un étudiant</h2>
    <form method="POST">
        <input type="text" name="code_etudiant" placeholder="ID ou matricule scanné" required>
        <button type="submit">Enregistrer l'accès</button>
    </form>
    <?php if ($message): ?>
        <div class="msg"><?= $message ?></div>
    <?php endif; ?>
    <br>
    <a href="dashboard_agent.php">⬅️ Retour au tableau de bord</a>
</div>

</body>
</html>
