<?php
// src/Entity/Produits.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource]
class Produits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'float')]
    private ?float $prix = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $modelPath = null;

    #[ORM\ManyToOne(targetEntity: Rayon::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Rayon $rayon = null;

    // Getters et Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;
        return $this;
    }

    public function getModelPath(): ?string
    {
        return $this->modelPath;
    }

    public function setModelPath(?string $modelPath): self
    {
        $this->modelPath = $modelPath;
        return $this;
    }

    public function getRayon(): ?Rayon
    {
        return $this->rayon;
    }

    public function setRayon(?Rayon $rayon): self
    {
        $this->rayon = $rayon;
        return $this;
    }
}