<?php
$host = 'localhost';
$dbname = 'epresence';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Active les erreurs PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Connexion à la base de données réussie !";
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage();
    exit();
}
?>
