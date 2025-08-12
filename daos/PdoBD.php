<?php

namespace daos;
class PdoBD {

    private static $_serveur = 'localhost'; 
    private static $_port = '3307';
    private static $_bdd = 'gestion_mosaic_2024';
    private static $_user = 'root';
    private static $_mdp = '';
    private static $_monPdo = null;
    private static $_instance = null;

    private function __construct() {
        try {
            PdoBD::$_monPdo = new \PDO(
                "mysql:host=" . PdoBD::$_serveur . ";port=" . PdoBD::$_port . ";dbname=" . PdoBD::$_bdd,
                PdoBD::$_user,
                PdoBD::$_mdp
            );
            PdoBD::$_monPdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); 
            PdoBD::$_monPdo->query("SET CHARACTER SET utf8"); 
        } catch (\PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    public function _destruct() {
        PdoBD::$_monPdo = null;
    }

    public static function getInstance() {
        if (PdoBD::$_instance == null) {
            PdoBD::$_instance = new PdoBD();
        }
        return PdoBD::$_instance;
    }

    public static function getMonPdo() {
        return self::$_monPdo;
    }

}
