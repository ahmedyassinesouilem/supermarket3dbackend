<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RayonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RayonRepository::class)]
#[ApiResource]
class Rayon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Produits>
     */
    #[ORM\OneToMany(targetEntity: Produits::class, mappedBy: 'rayon')]
    private Collection $produits;

    /**
     * @var Collection<int, Etagers>
     */
    #[ORM\OneToMany(targetEntity: Etagers::class, mappedBy: 'rayon')]
    private Collection $etagers;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
        $this->etagers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Produits>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produits $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setRayon($this);
        }

        return $this;
    }

    public function removeProduit(Produits $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getRayon() === $this) {
                $produit->setRayon(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Etagers>
     */
    public function getEtagers(): Collection
    {
        return $this->etagers;
    }

    public function addEtager(Etagers $etager): static
    {
        if (!$this->etagers->contains($etager)) {
            $this->etagers->add($etager);
            $etager->setRayon($this);
        }

        return $this;
    }

    public function removeEtager(Etagers $etager): static
    {
        if ($this->etagers->removeElement($etager)) {
            // set the owning side to null (unless already changed)
            if ($etager->getRayon() === $this) {
                $etager->setRayon(null);
            }
        }

        return $this;
    }
}
