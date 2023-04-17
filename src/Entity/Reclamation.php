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

    #[ORM\Column(name: "client_id")]
    private ?int $client_id = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?User $admin = null;

    #[ORM\Column(length: 255)]
    private  $image;


    #[ORM\ManyToOne(targetEntity: TypeReclamation::class, inversedBy: 'reclamations')]
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
        
    $words = explode(' ', trim($description));

    if (count($words) <= 2) {
        $this->description = $description;

        throw new \Exception('Description must contain at least 3 words.');
    }

    $this->description = $description;

    return $this;
    }

    public function getCommande(): ?Commande
    {
        return $this->commande;
    }

    public function setCommande(?Commande $commande): self
    {
        $this->commande = $commande;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;

        return $this;
    }

    public function getClientId(): ?int
    {
        return $this->client_id;
    }

    public function setClientId(?int $client_id): self
    {
        $this->client_id = $client_id;

        return $this;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): self
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
    
    public function getimage(): ?string
    {
        return $this->image;
    }

    public function setimage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }
    // ...

  


}
