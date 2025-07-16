<?php
session_start();
include("includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST['role'];
    $identifiant = $_POST['identifiant'];
    $motdepasse = $_POST['motdepasse'];

    // Chercher l'utilisateur dans la table `users`
    $stmt = $conn->prepare("SELECT * FROM users WHERE identifiant = :id AND role = :role AND statut = 1");
    $stmt->execute([
        'id' => $identifiant,
        'role' => $role
    ]);

    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur && password_verify($motdepasse, $utilisateur['motdepasse'])) {
        $_SESSION['utilisateur'] = $utilisateur;

        // Redirection selon le rôle
        header("Location: " . $role . "_dashboard.php");
        exit();
    } else {
        echo "<script>alert('Identifiants incorrects.'); window.location.href='login.html';</script>";
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
