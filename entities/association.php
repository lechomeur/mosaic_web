<?php
namespace entities;
#[\AllowDynamicProperties]
class association implements \JsonSerializable {
    private int $id;
    private string $nom;
    private string $image;

    // Constructeur
    public function __construct(int $id, string $nom,string $image) {
        $this->id = $id;
        $this->nom = $nom;
        $this->image= $image;
    }
    // Getters
    public function getNom(): string {
        return $this->nom;
    }
    public function getImage(): string {
        return $this->image;
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
