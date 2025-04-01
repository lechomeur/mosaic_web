<?php
session_start(); // Assure que la session est bien démarrée
session_destroy(); // Détruit la session
header("Location: index.php"); // Redirige vers la page d'accueil
exit(); // Stoppe l'exécution du script
