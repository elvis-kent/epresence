<?php
require_once 'config.php';

$filiere = $_GET['filiere'] ?? '';

if (!$filiere) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT nom FROM promotions WHERE filiere = ?");
$stmt->bind_param("s", $filiere);
$stmt->execute();
$result = $stmt->get_result();

$promotions = [];
while ($row = $result->fetch_assoc()) {
    $promotions[] = $row['nom'];
}

echo json_encode($promotions);
