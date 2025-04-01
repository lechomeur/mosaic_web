<?php

namespace entities;

#[\AllowDynamicProperties]
class adherent
{
    private ?int $Id = null;
    private ?string $Nom = null;
    private ?string $Prenom = null;
    private ?string $Adresse = null;
    private ?string $Mail = null;
    private ?float $Montant = null;
    private ?string $Cheque_Espece = null;
    private ?string $Telephone = null;
    private ?string $Date_Adhesion = null;
    private ?int $Association_Id = null;
    public ?string $AssociationNom = null; // Cette propriété doit être publique
    private ?string $Genre; // Nouvelle propriété
    private ?int $Age; // Nouvelle propriété
    private ?string $Attestation; // Nouvelle propriété

    // Constructeur pour mapper les colonnes SQL aux propriétés
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->Id = $data['Id'] ?? null;
            $this->Nom = $data['Nom'] ?? null;
            $this->Prenom = $data['Prenom'] ?? null;
            $this->Adresse = $data['Adresse'] ?? null;
            $this->Mail = $data['Mail'] ?? null;
            $this->Montant = $data['Montant'] ?? null;
            $this->Cheque_Espece = $data['Cheque_Espece'] ?? null;
            $this->Telephone = $data['Telephone'] ?? null;
            $this->Date_Adhesion = $data['Date_Adhesion'] ?? null;
            $this->Association_Id = $data['Association_Id'] ?? null;
            $this->AssociationNom = $data['AssociationNom'] ?? null;
            $this->Genre = $data['Genre'] ?? null; // Initialisation de la nouvelle propriété
            $this->Age = $data['Age'] ?? null; // Initialisation de la nouvelle propriété
            $this->Attestation = $data['Attestation'] ?? null; // Initialisation de la nouvelle prop
        }
    }


    // Getters et setters pour chaque propriété
    public function getId(): ?int
    {
        return $this->Id;
    }


    public function getNom(): ?string
    {
        return $this->nom ?? '';  // Si $nom est null, retourne une chaîne vide
    }

    public function getPrenom(): ?string
    {
        return $this->Prenom;
    }

    public function getAdresse(): ?string
    {
        return $this->Adresse;
    }

    public function getMail(): ?string
    {
        return $this->Mail;
    }

    public function getMontant(): ?float
    {
        return $this->Montant;
    }

    public function getChequeEspece(): ?string
    {
        return $this->Cheque_Espece;
    }

    public function getTelephone(): ?string
    {
        return $this->Telephone;
    }

    public function getDateAdhesion(): ?string
    {
        return $this->Date_Adhesion;
    }

    public function getAssociationId(): ?int
    {
        return $this->Association_Id;
    }
    public function setNom(string $nom): void
    {
        $this->Nom = $nom; // Utilisez $this->Nom au lieu de $this->nom
    }

    public function setId(int $id): void
    {
        $this->Id = $id; // Utilisez $this->Id au lieu de $this->id
    }

    public function setPrenom(string $prenom): void
    {
        $this->Prenom = $prenom; // Utilisez $this->Prenom au lieu de $this->prenom
    }

    public function setAdresse(string $adresse): void
    {
        $this->Adresse = $adresse; // Utilisez $this->Adresse au lieu de $this->adresse
    }

    public function setMail(string $mail): void
    {
        $this->Mail = $mail; // Utilisez $this->Mail au lieu de $this->mail
    }

    public function setMontant(float $montant): void
    {
        $this->Montant = $montant; // Utilisez $this->Montant au lieu de $this->montant
    }

    public function setChequeEspece(?string $chequeEspece): void
    {
        $this->Cheque_Espece = ($chequeEspece === null || $chequeEspece === "0") ? "" : $chequeEspece; // Utilisez $this->Cheque_Espece
    }

    public function setTelephone(string $telephone): void
    {
        $this->Telephone = $telephone; // Utilisez $this->Telephone au lieu de $this->telephone
    }

    public function setDateAdhesion(string $dateAdhesion): void
    {
        $this->Date_Adhesion = $dateAdhesion; // Utilisez $this->Date_Adhesion au lieu de $this->dateAdhesion
    }

    public function setAssociationNom(string $associationNom): void
    {
        $this->AssociationNom = $associationNom; // Utilisez $this->AssociationNom au lieu de $this->associationNom
    }

    public function setAssociationId(int $associationId): void
    {
        $this->Association_Id = $associationId; // Utilisez $this->Association_Id au lieu de $this->associationId
    }

    // Getters et setters pour les nouvelles propriétés
    public function getGenre(): ?string
    {
        return $this->Genre;
    }

    public function setGenre(?string $genre): void
    {
        $this->Genre = $genre;
    }

    public function getAge(): ?int
    {
        return $this->Age;
    }

    public function setAge(?int $age): void
    {
        $this->Age = $age;
    }

    public function getAttestation(): ?string
    {
        return $this->Attestation;
    }

    public function setAttestation(?string $attestation): void
    {
        $this->Attestation = $attestation;
    }

    // Conversion en tableau
    public function toArray(): array
    {
        return [
            'Id' => $this->Id,
            'Nom' => $this->Nom,
            'Prenom' => $this->Prenom,
            'Adresse' => $this->Adresse,
            'Mail' => $this->Mail,
            'Montant' => $this->Montant,
            'Cheque_Espece' => $this->Cheque_Espece,
            'Telephone' => $this->Telephone,
            'Date_Adhesion' => $this->Date_Adhesion,
            'Association_Id' => $this->Association_Id,
            'Genre' => $this->Genre,
            'Age' => $this->Age,
            'Attestation' => $this->Attestation
        ];
    }
}
