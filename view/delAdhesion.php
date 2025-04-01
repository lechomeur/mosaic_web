<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <!-- Lien vers les icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Lien vers le fichier CSS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="style/adhesion.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body>
    <span id="close-btn" class="close-btn">×</span>
    <p>Voulez-vous vraiment supprimer l'adhésion ?</p>
    <!-- Le lien de confirmation. On utilise "echo" via la syntaxe courte -->
    <a href="index.php?controller=adhesion&action=delete&adhesion_id=<?= $adhesionId ?>&association_id=<?= $associationId ?>"
        id="confirm-delete"
        class="action-button">
        Supprimer
    </a>
</body>