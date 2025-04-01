<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <!-- Lien vers les icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Lien vers le fichier CSS -->
    <!-- Lien vers le fichier CSS -->
    <link rel="stylesheet" href="style/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<?php   
        include __DIR__ . '/../view/header.php';
        ?>
<body>
<section class="home-section">
    <div class="home-content">
        <h2>Quelle association voulez-vous consulter pour les actions ?</h2> <!-- Titre ajouté -->
        <div class="sections">
            <?php foreach ($associations as $association): ?>
                <div class="section">
                    <h3><i class="fas fa-users"></i> <?= htmlspecialchars($association->getNom()) ?></h3>
                    <!-- Affichage de l'image de l'association -->
                    <img src="image/<?= htmlspecialchars($association->getImage()) ?>" alt="Logo de l'association" class="association-logo">
                    <a href="?controller=evenement&action=getAllEvenementByAsso&association_id=<?= $association->getId() ?>" class="btn">Consulter les actions</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
</body>
</html>