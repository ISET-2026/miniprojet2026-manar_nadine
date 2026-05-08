<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\CategorieRecetteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorieRecetteRepository::class)]
#[UniqueEntity(fields: ['nom'], message: 'Cette catégorie existe déjà.')]
#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['categorie:read']],
)]
class CategorieRecette
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    #[Groups(['categorie:read', 'recette:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['categorie:read'])]
    private ?string $icone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['categorie:read'])]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: Recette::class, mappedBy: 'categorie')]
    private Collection $recettes;

    public function __construct()
    {
        $this->recettes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }

    public function getIcone(): ?string { return $this->icone; }
    public function setIcone(?string $icone): static { $this->icone = $icone; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getRecettes(): Collection { return $this->recettes; }
    public function addRecette(Recette $recette): static
    {
        if (!$this->recettes->contains($recette)) {
            $this->recettes->add($recette);
            $recette->setCategorie($this);
        }
        return $this;
    }
    public function removeRecette(Recette $recette): static
    {
        if ($this->recettes->removeElement($recette) && $recette->getCategorie() === $this) {
            $recette->setCategorie(null);
        }
        return $this;
    }

    public function __toString(): string { return ($this->icone ? $this->icone . ' ' : '') . ($this->nom ?? ''); }
}
