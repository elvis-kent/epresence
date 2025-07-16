<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion Étudiant</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      text-align: center;
      padding-top: 100px;
    }
    form {
      background: white;
      display: inline-block;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px #ccc;
    }
    input {
      padding: 10px;
      margin: 10px;
      width: 250px;
    }
  </style>
</head>
<body>
  <h2>🎓 Connexion Étudiant</h2>
  <form action="etudiant_dashboard.php" method="GET">
    <input type="text" name="matricule" placeholder="Entrez votre matricule" required><br>
    <button type="submit">Se connecter</button>
  </form>
</body>
</html>
