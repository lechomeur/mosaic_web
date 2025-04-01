<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <!-- Lien vers les icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Lien vers le fichier CSS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style/addAdhesion.css">
</head>
<?php
include __DIR__ . '/../view/header.php';
?>
<body>
    <div class="container-wrapper">
        <div class="container">
            <div class="signin-signup">
                <!-- Formulaire d'ajout d'adhésion -->
                <form id="loginForm" action="index.php?controller=evenement&action=add&association_id=<?= htmlspecialchars($associationId) ?>" method="POST" class="sign-in-form" enctype="multipart/form-data">
                    <h2 class="title">Ajouter une action</h2>
                    <div class="input-field">
                        <input type="text" name="nom" placeholder="Nom" required>
                        <input type="hidden" name="association_id" id="associationId" value="<?= htmlspecialchars($associationId) ?>">
                        <input type="text" name="lieu" placeholder="Nom de l'action" required>
                        <input type="text" name="lieu" placeholder="Lieu de l'action">
                        <input type="number" name="cotisation" placeholder="Montant de la cotisation">
                    </div>
                <input type="submit" value="Ajouter" class="btn">
                </form>
            </div>
        </div>
    </div>
</body>

</html>