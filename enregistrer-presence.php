<?php
require 'config.php';

// Lire le JSON brut
$data = json_decode(file_get_contents("php://input"), true);

$etudiant_id = $data['etudiant_id'];
$cours_id = $data['cours_id'];
$date = date('Y-m-d');
$heure = date('H:i:s');

// Vérifier doublon
$check = $conn->prepare("SELECT * FROM presences WHERE etudiant_id = ? AND cours_id = ? AND date_presence = ?");
$check->bind_param("iis", $etudiant_id, $cours_id, $date);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    echo "⚠️ Déjà scanné aujourd'hui.";
} else {
    $stmt = $conn->prepare("INSERT INTO presences (etudiant_id, cours_id, date_presence, heure) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $etudiant_id, $cours_id, $date, $heure);
    if ($stmt->execute()) {
        echo "✅ Présence enregistrée avec succès.";
    } else {
        echo "❌ Erreur d'enregistrement.";
    }
}
