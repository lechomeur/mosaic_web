<?php

namespace Entities;

#[\AllowDynamicProperties]
class adherent implements \JsonSerializable {
    private int $id;
    private string $nom;
    private string $prenom;
    private string $adresse;
    private string $mail;
    private int $montant;
    private string $chequeEspece;
    private string $telephone;
    private string $dateAdhesion;
    private string $associationNom;
    private int $associationId;

    // Constructeur
    public function __construct(
        int $id = 0,
        string $nom = '',
        string $prenom = '',
        string $adresse = '',
        string $mail = '',
        int $montant = 0,
        string $chequeEspece = '',
        string $telephone = '',
        string $dateAdhesion = '',
        string $associationNom = '',
        int $associationId = 0
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->adresse = $adresse;
        $this->mail = $mail;
        $this->montant = $montant;
        $this->chequeEspece = $chequeEspece;
        $this->telephone = $telephone;
        $this->dateAdhesion = $dateAdhesion;
        $this->associationNom = $associationNom;
        $this->associationId = $associationId;
    }

    // Getters et Setters
    public function getId(): int {
        return $this->id;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function getAdresse(): string {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): void {
        $this->adresse = $adresse;
    }

    public function getMail(): string {
        return $this->mail;
    }

    public function setMail(string $mail): void {
        $this->mail = $mail;
    }

    public function getMontant(): int {
        return $this->montant;
    }

    public function setMontant(int $montant): void {
        $this->montant = $montant;
    }

    public function getChequeEspece(): string {
        return $this->chequeEspece;
    }
    public function setChequeEspece(?string $chequeEspece): void {
        $this->chequeEspece = ($chequeEspece === null || $chequeEspece === "0") ? "" : $chequeEspece;
    }
    
    
    public function getTelephone(): string {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): void {
        $this->telephone = $telephone;
    }

    public function getDateAdhesion(): string {
        return $this->dateAdhesion;
    }

    public function setDateAdhesion(string $dateAdhesion): void {
        $this->dateAdhesion = $dateAdhesion;
    }

    public function getAssociationNom(): string {
        return $this->associationNom;
    }

    public function setAssociationNom(string $associationNom): void {
        $this->associationNom = $associationNom;
    }

    public function getAssociationId(): int {
        return $this->associationId;
    }

    public function setAssociationId(int $associationId): void {
        $this->associationId = $associationId;
    }

    // Sérialisation JSON
    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'adresse' => $this->adresse,
            'mail' => $this->mail,
            'montant' => $this->montant,
            'chequeEspece' => $this->chequeEspece,
            'telephone' => $this->telephone,
            'dateAdhesion' => $this->dateAdhesion,
            'associationNom' => $this->associationNom,
            'associationId' => $this->associationId
        ];
    }
    
    // Conversion en tableau
    public function toArray(): array {
        return get_object_vars($this);
    }
}
