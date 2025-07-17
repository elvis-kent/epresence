<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    echo "Accès refusé";
    exit;
}

$prof_id = $_SESSION['id'];
$faculte = $_GET['faculte'] ?? '';
$departement = $_GET['departement'] ?? '';
$filiere = $_GET['filiere'] ?? '';
$promotion = $_GET['promotion'] ?? '';
$cours = $_GET['cours'] ?? '';

if (!$faculte || !$departement || !$filiere || !$promotion || !$cours) {
    echo "<tr><td colspan='4'>Sélection incomplète</td></tr>";
    exit;
}

// Récupérer les présences du prof, cours, date actuelle
$date = date('Y-m-d');

$stmt = $conn->prepare(
    "SELECT e.nom, p.date_presence, p.heure_presence, p.cours 
    FROM presences p
    JOIN etudiants e ON p.etudiant_id = e.id
    WHERE p.prof_id = ? AND p.cours = ? AND p.date_presence = ?"
);
$stmt->bind_param("iss", $prof_id, $cours, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<tr><td colspan='4'>Aucune présence enregistrée aujourd'hui</td></tr>";
    exit;
}

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['nom']) . "</td>";
    echo "<td>" . htmlspecialchars($row['date_presence']) . "</td>";
    echo "<td>" . htmlspecialchars($row['heure_presence']) . "</td>";
    echo "<td>" . htmlspecialchars($row['cours']) . "</td>";
    echo "</tr>";
}
