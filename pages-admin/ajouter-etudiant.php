<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Ajouter un étudiant</title>
</head>
<body>
  <h2>Ajouter un étudiant</h2>
  <form action="traitement_etudiant.php" method="POST" enctype="multipart/form-data">
    <label>Nom complet :</label><br>
    <input type="text" name="nom" required><br><br>

    <label>Matricule :</label><br>
    <input type="text" name="matricule" required><br><br>

    <label>Faculté :</label><br>
    <input type="text" name="faculte" required><br><br>

    <label>Département :</label><br>
    <input type="text" name="departement" required><br><br>

    <label>Filière :</label><br>
    <input type="text" name="filiere" required><br><br>

    <label>Promotion :</label><br>
    <input type="text" name="promotion" required><br><br>

    <label>Photo :</label><br>
    <input type="file" name="photo" accept="image/*" required><br><br>

    <button type="submit">Créer l’étudiant</button>
  </form>
</body>
</html>
