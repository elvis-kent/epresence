<?php
require_once 'config.php';

$departement = $_GET['departement'] ?? '';

if (!$departement) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT nom FROM filieres WHERE departement = ?");
$stmt->bind_param("s", $departement);
$stmt->execute();
$result = $stmt->get_result();

$filieres = [];
while ($row = $result->fetch_assoc()) {
    $filieres[] = $row['nom'];
}

echo json_encode($filieres);
