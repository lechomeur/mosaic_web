<?php

namespace daos;
class PdoBD {

    private static $_serveur = 'localhost'; // Corrigé en 'localhost'
    private static $_port = '3307'; // Mise à jour du port pour MariaDB
    private static $_bdd = 'gestion_mosaic_2024';
    private static $_user = 'root';
    private static $_mdp = '';
    private static $_monPdo = null;
    private static $_instance = null;

    private function __construct() {
        try {
            // Utilisation de mysql pour la connexion PDO à MariaDB
            PdoBD::$_monPdo = new \PDO(
                "mysql:host=" . PdoBD::$_serveur . ";port=" . PdoBD::$_port . ";dbname=" . PdoBD::$_bdd,
                PdoBD::$_user,
                PdoBD::$_mdp
            );
            PdoBD::$_monPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); // Active les exceptions
            PdoBD::$_monPdo->query("SET CHARACTER SET utf8"); // Définit l'encodage UTF-8
        } catch (\PDOException $e) {
            // Gestion d'erreur en cas de problème de connexion
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    // Destructeur pour fermer la connexion
    public function _destruct() {
        PdoBD::$_monPdo = null;
    }

    // Singleton pour s'assurer qu'il n'y a qu'une seule instance de connexion
    public static function getInstance() {
        if (PdoBD::$_instance == null) {
            PdoBD::$_instance = new PdoBD();
        }
        return PdoBD::$_instance;
    }

    // Méthode pour récupérer l'instance PDO
    public static function getMonPdo() {
        return self::$_monPdo;
    }

}
