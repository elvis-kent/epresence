<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$email || !$password) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $conn->prepare("SELECT id, nom, mot_de_passe FROM professeurs WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $prof = $result->fetch_assoc();

            // Ici, mot_de_passe est supposé hashé avec password_hash()
            if (password_verify($password, $prof['mot_de_passe'])) {
                $_SESSION['id'] = $prof['id'];
                $_SESSION['nom'] = $prof['nom'];
                $_SESSION['role'] = 'professeur';
                header("Location: scan_presence.php");
                exit;
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8" /><title>Login Professeur</title></head>
<body>
<h2>Connexion Professeur</h2>
<?php if (isset($error)): ?>
<p style="color:red;"><?=htmlspecialchars($error)?></p>
<?php endif; ?>
<form method="post" action="">
<label>Email : <input type="email" name="email" required></label><br><br>
<label>Mot de passe : <input type="password" name="password" required></label><br><br>
<button type="submit">Se connecter</button>
</form>
</body>
</html>
