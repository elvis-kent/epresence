<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent_biblio') {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $code_qr = $_POST['code_qr'] ?? '';

    // Rechercher l'étudiant à partir du QR scanné
    $stmt = $conn->prepare("SELECT id, nom, prenom FROM etudiants WHERE qr_code = ?");
    $stmt->bind_param("s", $code_qr);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $etudiant = $result->fetch_assoc();
        $etudiant_id = $etudiant['id'];

        // Enregistrer l'accès
        $stmt2 = $conn->prepare("INSERT INTO acces_service (etudiant_id, service) VALUES (?, 'bibliothèque')");
        $stmt2->bind_param("i", $etudiant_id);
        $stmt2->execute();

        $message = "✅ Accès accordé à " . htmlspecialchars($etudiant['prenom']) . " " . htmlspecialchars($etudiant['nom']);
    } else {
        $message = "❌ Étudiant introuvable ou QR invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scan accès bibliothèque</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .container { width: 400px; margin: auto; background: white; padding: 20px; border-radius: 6px; box-shadow: 0 0 5px #ccc; }
        h2 { text-align: center; }
        input[type="text"] { width: 100%; padding: 10px; margin-top: 10px; margin-bottom: 20px; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; }
        .message { text-align: center; margin-top: 15px; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <h2>📖 Scanner pour entrer</h2>

    <form method="post">
        <label>Scanner le QR Code de l'étudiant :</label>
        <input type="text" name="code_qr" placeholder="QR code" autofocus required>
        <button type="submit">🛂 Vérifier</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>
</div>

</body>
</html>
