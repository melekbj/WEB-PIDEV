<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?Commande $commande = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?User $admin = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?TypeReclamation $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCommande(): ?commande
    {
        return $this->commande;
    }

    public function setCommande(?commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getProduit(): ?produit
    {
        return $this->produit;
    }

    public function setProduit(?produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getClient(): ?user
    {
        return $this->client;
    }

    public function setClient(?user $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getAdmin(): ?user
    {
        return $this->admin;
    }

    public function setAdmin(?user $admin): self
    {
        $this->admin = $admin;

        return $this;
    }

    public function getType(): ?TypeReclamation
    {
        return $this->type;
    }

    public function setType(?TypeReclamation $type): self
    {
        $this->type = $type;

        return $this;
    }
}
