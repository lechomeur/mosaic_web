<?php
namespace entities;
#[\AllowDynamicProperties]
class association implements \JsonSerializable {
    private int $id;
    private string $nom;

    // Constructeur
    public function __construct(int $id, string $nom) {
        $this->id = $id;
        $this->nom = $nom;
    }

    // Getters
    public function getNom(): string {
        return $this->nom;
    }

    public function getId(): int {
        return $this->id;
    }

    // Setters
    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    // Implémentation de jsonSerialize
    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'nom' => $this->nom
        ];
    }
}
