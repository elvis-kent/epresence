<?php
require_once 'config.php';

$faculte = $_GET['faculte'] ?? '';

if (!$faculte) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT nom FROM departements WHERE faculte = ?");
$stmt->bind_param("s", $faculte);
$stmt->execute();
$result = $stmt->get_result();

$departements = [];
while ($row = $result->fetch_assoc()) {
    $departements[] = $row['nom'];
}

echo json_encode($departements);
