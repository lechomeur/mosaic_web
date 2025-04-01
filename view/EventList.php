<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Liste des Événements</title>
    <link rel="stylesheet" href="style/eventList.css">
</head>
<?php
include __DIR__ . '/../view/header.php';
$formatter = new IntlDateFormatter(
    'fr_FR',               // Locale (français)
    IntlDateFormatter::FULL, // Style de date (FULL, LONG, MEDIUM, SHORT)
    IntlDateFormatter::NONE, // Style de temps (NONE pour ignorer l'heure)
    null,                  // Timezone (null pour utiliser la timezone par défaut)
    null,                  // Calendar type (null pour le calendrier grégorien)
    'd MMMM Y'             // Format personnalisé (d = jour, MMMM = mois en lettres, Y = année)
);
?>

<body>
    <div class="evenement-container">
        <h1>Liste des Événements</h1>
        <?php foreach ($evenements as $evenement):
            $dateFormatee = $formatter->format($evenement->getDateEvenement()->getTimestamp());
        ?>
            <div class="event-container">
                <a href="controller=evenement&action=getEventById&id=<?= $evenement->getId() ?>" class="event-link">
                    <div class="event-title"><?= htmlspecialchars($evenement->getTitre()) ?></div>
                    <div class="event-date"><?= $dateFormatee ?></div>
                    <div class="event-status <?= $evenement->getDateEvenement() < new DateTime() ? 'status-terminated' : 'status-in-progress' ?>">
                        Statut: <?= $evenement->getDateEvenement() < new DateTime() ? 'Terminé' : 'En cours' ?>
                    </div>
                </a>

                <!-- Conteneur des boutons -->
                <div class="button-container">
                    <!-- Bouton Consulter -->
                    <a href="controller=evenement&action=getEventById&id=<?= $evenement->getId() ?>" class="consult-button">👁 Consulter</a>

                    <!-- Bouton Supprimer -->
                    <form action="controller=evenement&action=deleteEvent" method="POST" class="delete-form">
                        <input type="hidden" name="id" value="<?= $evenement->getId() ?>">
                        <button type="submit" class="delete-button" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?')">🗑 Supprimer</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Icône "Ajouter" -->
        <a href="controller=evenement&action=addEvent&association_id=<?= $associationId ?>" class="event-link add-event">
            <div class="event-container add-event-container">
                <i class="fas fa-plus-circle"></i> <!-- Icône FontAwesome -->
                <span>Ajouter un événement</span>
            </div>
        </a>
    </div>
</body>

</html>