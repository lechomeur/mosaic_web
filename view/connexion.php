<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mosaic</title>
    <link rel="stylesheet" href="style/login.css">

</head>

<body>
    <div class="container-wrapper">
        <div class="container">
            <div class="signin-signup">
                <form id="" action="index.php" method="POST" class="sign-in-form">
                    <h2 class="title">Connectez-vous</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input type="text" name="login" placeholder="nom d'utilisateur" required>
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="mdp" placeholder="mot de passe" required>
                    </div>
                    <input type="submit" value="connexion" class="btn">
                    <!-- Affichage des messages d'erreur s'il y en a -->
                    <?php
                    if (isset($_SESSION['error_message'])) {
                        echo '<div class="error-message" style="color: red;">' . $_SESSION['error_message'] . '</div>';
                        unset($_SESSION['error_message']); // On vide le message après l'affichage
                    }
                    ?>
                </form>
            </div>
            <div class="panels-container">
                <div class="panel right-panel">
                    <div class="content">
                        <h3></h3>
                        <p></p>
                    </div>
                    <img src="image/association_1.png" alt="" class="image">
                </div>
            </div>
        </div>
    </div>
</body>

</html>