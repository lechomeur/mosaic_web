<?php

namespace Controller;

use daos\utilisateurDaos;

header('Content-Type: application/json; charset=utf-8');
class utilisateurController
{
    public function __construct()
    {
    }

    function connexion()
    {
        $login = filter_input(INPUT_GET, "login", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mdp = filter_input(INPUT_GET, "mdp", FILTER_SANITIZE_SPECIAL_CHARS);
        if (!$login || !$mdp) {
            echo json_encode(["success" => false, "message" => "Identifiants requis"]);
            exit;
        }
        $utilisateur_connecte = utilisateurDaos::identifier($login, $mdp);

        if ($utilisateur_connecte) {
            $_SESSION['utilisateur'] = $utilisateur_connecte;
            echo json_encode(["success" => true, "message" => "Connexion reussie"]);
        } else {
            echo json_encode(["success" => false, "message" => "Identifiants incorrects"]);
        }
    }
   public function deconnexion()
    {
        session_destroy();
        echo json_encode(["success" => true, "message" => "Deconnexion reussie"]);
    }
}
