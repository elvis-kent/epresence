<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_faculte') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: liste_cours.php");
    exit();
}

$cours_id = intval($_GET['id']);

// Vérifier que le cours appartient bien à la faculté de l'admin
$faculte_id = $_SESSION['faculte_id'];

$stmt = $conn->prepare("SELECT c.id FROM cours c
    JOIN promotions p ON c.promotion_id = p.id
    JOIN filieres f ON p.filiere_id = f.id
    JOIN departements d ON f.departement_id = d.id
    JOIN facultes fa ON d.faculte_id = fa.id
    WHERE c.id = ? AND fa.id = ?");
$stmt->bind_param("ii", $cours_id, $faculte_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Le cours n'existe pas ou pas dans la faculté de l'admin
    header("Location: liste_cours.php");
    exit();
}

// Suppression du cours
$stmt_del = $conn->prepare("DELETE FROM cours WHERE id = ?");
$stmt_del->bind_param("i", $cours_id);
$stmt_del->execute();

header("Location: liste_cours.php?msg=deleted");
exit();
