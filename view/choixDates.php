<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <!-- Lien vers les icônes Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style/menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
<section class="home-section">
    <div class="home-content">
        <h2>Choisissez les dates que vous souhaitez consulter pour les adhésions</h2>
        <div class="sections">
            <?php foreach ($dateAnneeAdhesion as $dateAnnee): ?>
                <div class="section">
                    <!-- Affichage de l'année -->
                    <h3><i class="fas fa-users"></i> <?= htmlspecialchars($dateAnnee) ?></h3>

                    <!-- Affichage de l'icône de calendrier (Material Icons) -->
                    <i class="material-icons">event</i>

                    <!-- Lien vers la page de consultation des adhésions -->
                    <a href="?controller=adhesion&action=choisirDateAdhesion&association_id=<?= $association->getId() ?>&année=<?= htmlspecialchars($dateAnnee) ?>" class="btn">Consulter les adhésions</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


</body>

</html>