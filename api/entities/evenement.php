<?php
namespace entities;

use DateTime;

class evenement implements \JsonSerializable {
    private int $id;
    private string $titre;
    private DateTime $dateEvenement; // Utilisation d'une string pour stocker la date
    private string $lieu;

    // Constructeur
    public function __construct() {
        
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getTitre(): string {
        return $this->titre;
    }

    public function getDateEvenement(): DateTime {
        return $this->dateEvenement;
    }

    public function getLieu(): string {
        return $this->lieu;
    }

    // Setters
    public function setTitre(string $titre): void {
        $this->titre = $titre;
    }
    public function setId($id) {
        $this->id = $id;
    }

    public function setDateEvenement(DateTime $dateEvenement): void {
        $this->dateEvenement = $dateEvenement;
    }

    public function setLieu(string $lieu): void {
        $this->lieu = $lieu;
    }

    // Implémentation de jsonSerialize
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'dateEvenement' => $this->dateEvenement,
            'lieu' => $this->lieu
        ];
    }
}
