<?php
namespace entities;

class participation implements \JsonSerializable {
    private Evenement $evenement;
    private Participant $participant;

    // Constructeur
    public function __construct(Evenement $evenement, Participant $participant) {
        $this->evenement = $evenement;
        $this->participant = $participant;
    }

    // Getters
    public function getEvenement(): Evenement {
        return $this->evenement;
    }

    public function getParticipant(): Participant {
        return $this->participant;
    }

    // Setters
    public function setEvenement(Evenement $evenement): void {
        $this->evenement = $evenement;
    }

    public function setParticipant(Participant $participant): void {
        $this->participant = $participant;
    }

    // Implémentation de jsonSerialize
    public function jsonSerialize(): mixed {
        return [
            'evenement' => $this->evenement,
            'participant' => $this->participant
        ];
    }
}
