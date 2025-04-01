<?php
namespace entities ;
#[\AllowDynamicProperties]

class utilisateur implements \JsonSerializable  {

    private $id;
    private $nom ;
    private $prenom ;
    private $login;
    private $mdp;

    public function __construct(){
         
     }
    
     public function getIdUser() {
        return $this->id;
    } 
     public function getMdp(){
        return $this->mdp;
    } 
    public function getLogin() {
        return $this->login;
    }

    public function getNom() {
        return $this->nom;
    }

    public function getPrenom() {
        return $this->prenom;
    }

    public function setLogin($login): void {
        $this->login = $login;
    }
    public function setMdp($mdp): void {
        $this->mdp = $mdp;
    }

    public function setNom($nom): void {
        $this->nom = $nom;
    }

    public function setPrenom($prenom): void {
        $this->prenom = $prenom;
    }
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'login' => $this->login
            // Ne pas inclure le mot de passe pour des raisons de sécurité
        ];
    }
    

}