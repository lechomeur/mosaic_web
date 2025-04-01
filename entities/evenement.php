<?php
namespace entities;

use DateTime;

class evenement {
    private int $Id = 0;
    private string $Titre = '';
    private ?DateTime $Date_Evenement = null;
    private string $lieu = '';
    private int $Association = 0;

    // Constructeur
    public function __construct(int $Id = 0, string $Titre = '', ?DateTime $Date_Evenement = null, string $lieu = '', int $Association = 0) {
        $this->Id = $Id;
        $this->Titre = $Titre;
        $this->Date_Evenement = $Date_Evenement;
        $this->lieu = $lieu;
        $this->Association = $Association;
    }

    // Getters
    public function getId(): int {
        return $this->Id;
    }

    public function getTitre(): string {
        return $this->Titre;
    }

    public function getDateEvenement(): ?DateTime {
        return $this->Date_Evenement;
    }

    public function getLieu(): string {
        return $this->lieu;
    }

    public function getAssociation(): int {
        return $this->Association;
    }

    // Setters
    public function setTitre(string $Titre): void {
        $this->Titre = $Titre;
    }

    public function setId(int $Id): void {
        $this->Id = $Id;
    }

    public function setDateEvenement($Date_Evenement): void {
        if (is_string($Date_Evenement)) {
            $this->Date_Evenement = new DateTime($Date_Evenement);
        } elseif ($Date_Evenement instanceof DateTime) {
            $this->Date_Evenement = $Date_Evenement;
        } else {
            $this->Date_Evenement = null; // Ou lancer une exception
        }
    }

    public function setLieu(string $lieu): void {
        $this->lieu = $lieu;
    }

    public function setAssociation(int $Association): self {
        $this->Association = $Association;
        return $this;
    }
}