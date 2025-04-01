<?php

namespace controller;
use daos\utilisateurDaos;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



class utilisateurController
{
    public function __construct($action)
    {
        $request = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        switch ($action) {
            case "connexion":
                if ($request === 'POST') {
                    $this->connexion();
                } else {
                    $this->afficherPageConnexion();
                }
                break;
            default:
                echo "Action non traitée dans le controleur commun", "index.php";
                break;
        }
    }
    function afficherPageConnexion(){
        include __DIR__ .'/../view/connexion.php'; // Toujours inclure une seule fois
    }    function connexion()
    {    
        $login = filter_input(INPUT_POST, "login", FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $mdp = filter_input(INPUT_POST, "mdp", FILTER_SANITIZE_SPECIAL_CHARS);
    
        var_dump("Tentative de connexion", $login, $mdp); // DEBUG
    
        if (!$login || !$mdp) {
            $_SESSION['error_message'] = "Identifiants requis";
            header("Location: index.php");
            exit;
        }
        $utilisateur_connecte = utilisateurDaos::identifier($login, $mdp);
    
        if ($utilisateur_connecte) {
            $_SESSION['utilisateur'] = $utilisateur_connecte;
            var_dump("Utilisateur connecté :", $_SESSION['utilisateur']); // DEBUG
            header("Location:index.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Identifiants incorrects";
            header("Location: index.php");
            exit;
        }
    }    
    
   public function deconnexion()
    {
        session_destroy();
        header("Location: index.php");
    }
}
