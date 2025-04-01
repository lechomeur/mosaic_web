<?php
namespace entities;

class participant implements \JsonSerializable {
    private int $id;
    private string $nom;
    private string $prenom;
    private int $age;
    private string $telephone;
    private int $montant;
    private string $chequeEspece;
    private string $adherant;
    private string $genre;
    private string $mail;

    // Constructeur principal
    public function __construct(
        int $id = 0, 
        ?string $nom = null,  // Permet de ne pas initialiser ce champ si il n'est pas défini
        ?string $prenom = null, 
        int $age = 0, 
        ?string $telephone = null,
        int $montant = 0, 
        ?string $chequeEspece = null, 
        ?string $genre = null, 
        ?string $mail = null,
        ?string $adherant = null // Adherant mis à null par défaut
    ) {
        $this->id = $id;
        $this->nom = $nom ?? ''; // Si $nom est null, on l'initialise à une chaîne vide
        $this->prenom = $prenom ?? ''; 
        $this->age = $age;
        $this->telephone = $telephone ?? ''; 
        $this->montant = $montant;
        $this->chequeEspece = $chequeEspece ?? ''; 
        $this->genre = $genre ?? ''; 
        $this->mail = $mail ?? ''; 
        $this->adherant = $adherant ?? ''; // Si $adherant est null, on le garde à une chaîne vide
    }
    
    
    

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getAge(): int {
        return $this->age;
    }

    public function getTelephone(): string {
        return $this->telephone;
    }

    public function getMontant(): int {
        return $this->montant;
    }

    public function getChequeEspece(): string {
        return $this->chequeEspece;
    }

    public function getAdherant(): string {
        return $this->adherant;
    }

    public function getGenre(): string {
        return $this->genre;
    }

    public function getMail(): string {
        return $this->mail;
    }

    // Setters
    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function setAge(int $age): void {
        $this->age = $age;
    }

    public function setTelephone(string $telephone): void {
        $this->telephone = $telephone;
    }

    public function setMontant(int $montant): void {
        $this->montant = $montant;
    }

    public function setChequeEspece(string $chequeEspece): void {
        $this->chequeEspece = $chequeEspece;
    }

    public function setAdherant(string $adherant): void {
        $this->adherant = $adherant;
    }

    public function setGenre(string $genre): void {
        $this->genre = $genre;
    }

    public function setMail(string $mail): void {
        $this->mail = $mail;
    }

    // Implémentation de jsonSerialize
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'age' => $this->age,
            'telephone' => $this->telephone,
            'montant' => $this->montant,
            'chequeEspece' => $this->chequeEspece,
            'adherant' => $this->adherant,
            'genre' => $this->genre,
            'mail' => $this->mail
        ];
    }
}
