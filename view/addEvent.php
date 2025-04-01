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
                <form id="loginForm" action="index.php?controller=adhesion&action=add&association_id=<?= htmlspecialchars($associationId) ?>" method="POST" class="sign-in-form" enctype="multipart/form-data">
                    <h2 class="title">Ajouter une adhésion</h2>
                    <div class="input-field">
                        <input type="text" name="nom" placeholder="Nom" required>
                        <input type="hidden" name="association_id" id="associationId" value="<?= htmlspecialchars($associationId) ?>">
                        <input type="text" name="prenom" placeholder="Prénom" required>
                        <div class="genre-container">
                            <h3>Genre</h3>
                            <div class="radio-buttons">
                                <label class="radio-button">
                                    <input type="radio" name="genre" value="Homme">
                                    <span class="radio-custom"></span>
                                    Homme
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="genre" value="Femme">
                                    <span class="radio-custom"></span>
                                    Femme
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="genre" value="Autre">
                                    <span class="radio-custom"></span>
                                    Autre
                                </label>
                            </div>
                        </div>
                        <input type="number" name="age" placeholder="Âge" required>
                        <input type="text" name="mail" placeholder="Email">
                        <input type="text" name="numero" placeholder="Numéro de téléphone">
                        <input type="number" name="cotisation" placeholder="Montant de la cotisation">
                        <div class="paiement-container">
                            <h3>Moyen de paiement</h3>
                            <div class="radio-buttons">
                                <label class="radio-button">
                                    <input type="radio" name="moyenPaiement" value="Espèce">
                                    <span class="radio-custom"></span>
                                    Espèce
                                </label>
                                <label class="radio-button">
                                    <input type="radio" name="moyenPaiement" value="Chèque">
                                    <span class="radio-custom"></span>
                                    Chèque
                                </label>
                            </div>
                            <div class="upload-container">
                                <h2 class="title">Téléverser une attestation</h2>
                                <input type="file" name="attestation" accept=".pdf,.jpg,.png">
                            </div> 
                        </div>
                    </div>
                <input type="submit" value="Ajouter" class="btn">
                </form>
            </div>
        </div>
    </div>
</body>

</html>