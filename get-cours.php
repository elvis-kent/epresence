<?php
require_once 'config.php';

$promotion = $_GET['promotion'] ?? '';
$prof_id = $_GET['prof_id'] ?? 0;

if (!$promotion || !$prof_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT nom FROM cours WHERE promotion = ? AND professeur_id = ?");
$stmt->bind_param("si", $promotion, $prof_id);
$stmt->execute();
$result = $stmt->get_result();

$cours = [];
while ($row = $result->fetch_assoc()) {
    $cours[] = $row['nom'];
}

echo json_encode($cours);
