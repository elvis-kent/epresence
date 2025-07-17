<?php
require_once('includes/config.php');
session_start();

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin_universite') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // On vérifie si le professeur existe
    $verif = $conn->prepare("SELECT id FROM professeurs WHERE id = ?");
    $verif->bind_param("i", $id);
    $verif->execute();
    $verif->store_result();

    if ($verif->num_rows > 0) {
        // Suppression
        $stmt = $conn->prepare("DELETE FROM professeurs WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            header("Location: liste_professeurs.php?msg=supprimé");
            exit();
        } else {
            echo "❌ Erreur lors de la suppression.";
        }
        $stmt->close();
    } else {
        echo "❌ Professeur introuvable.";
    }

    $verif->close();
}
?>
