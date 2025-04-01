<?php

namespace daos;

use daos\PdoBD as DaosPdoBD;
use PdoBD;
require_once "PdoBD.php";


/**
 * @return [] \entities\utilisateur
 */
class UtilisateurDaos
{
    public static function identifier($login, $password)
    {
        $sql = "SELECT * FROM utilisateur WHERE login = :login AND mdp = SHA2(:mdp,256)";
        $stmt = DaosPdoBD::getInstance()->getMonPdo()->prepare($sql);
        $stmt->bindValue(":login", $login);
        $stmt->bindValue(":mdp", $password);
        $stmt->execute();
        $stmt->setFetchMode(\PDO::FETCH_CLASS, '\entities\utilisateur');
        $utilisateur = $stmt->fetch();
        return $utilisateur;
    }
    
}
