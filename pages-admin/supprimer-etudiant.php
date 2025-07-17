<?php
session_start();
require_once 'config.php';

// Vérification rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin_departement') {
    header("Location: login.php");
    exit;
}

// Vérifier que l'id est passé
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: liste_etudiants.php");
    exit;
}

$id = intval($_GET['id']);

// Récupérer l’étudiant avant suppression (pour log)
$stmt = $conn->prepare("SELECT nom, matricule FROM etudiants WHERE id = ? AND departement = ?");
$stmt->bind_param("is", $id, $_SESSION['departement']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    // Étudiant pas trouvé ou hors département
    header("Location: liste_etudiants.php");
    exit;
}
$etudiant = $result->fetch_assoc();

// Suppression
$stmtDel = $conn->prepare("DELETE FROM etudiants WHERE id = ? AND departement = ?");
$stmtDel->bind_param("is", $id, $_SESSION['departement']);
if ($stmtDel->execute()) {
    // Log action
    logAction($conn, $_SESSION['nom'], $_SESSION['role'], "Suppression de l’étudiant {$etudiant['nom']} ({$etudiant['matricule']})");
    header("Location: liste_etudiants.php?msg=deleted");
    exit;
} else {
    echo "Erreur lors de la suppression : " . $conn->error;
}
?>
