<?php
session_start();
require 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    header('Location: login.php');
    exit;
}

// Récupérer les cours du professeur
$prof_id = $_SESSION['id'];
$cours = [];

$stmt = $conn->prepare("SELECT id, nom FROM cours WHERE professeur_id = ?");
$stmt->bind_param("i", $prof_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $cours[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Scanner Présence</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        body { font-family: Arial; padding: 30px; background: #f0f0f0; }
        h2 { color: #222; }
        #qr-reader { width: 400px; margin: auto; }
        select, button {
            padding: 10px;
            width: 400px;
            margin: 10px auto;
            display: block;
        }
        #status {
            text-align: center;
            margin-top: 15px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

<h2>📷 Scanner un étudiant</h2>

<form id="form-cours">
    <label for="cours_id">Choisir un cours :</label>
    <select id="cours_id" required>
        <option value="">-- Sélectionner --</option>
        <?php foreach ($cours as $c): ?>
            <option value="<?= $c['id'] ?>"><?= $c['nom'] ?></option>
        <?php endforeach; ?>
    </select>
</form>

<div id="qr-reader"></div>
<p id="status"></p>

<script>
let selectedCours = null;

document.getElementById("cours_id").addEventListener("change", function () {
    selectedCours = this.value;
});

function onScanSuccess(decodedText, decodedResult) {
    if (!selectedCours) {
        document.getElementById("status").innerText = "❌ Veuillez d'abord sélectionner un cours.";
        return;
    }

    // Envoyer les données au serveur
    fetch("enregistrer_presence.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ etudiant_id: decodedText, cours_id: selectedCours })
    })
    .then(response => response.text())
    .then(result => {
        document.getElementById("status").innerText = result;
    })
    .catch(err => {
        document.getElementById("status").innerText = "❌ Erreur lors de l'enregistrement.";
    });
}

let qrScanner = new Html5Qrcode("qr-reader");
qrScanner.start(
    { facingMode: "environment" },
    {
        fps: 10,
        qrbox: { width: 250, height: 250 }
    },
    onScanSuccess
);
</script>

</body>
</html>
