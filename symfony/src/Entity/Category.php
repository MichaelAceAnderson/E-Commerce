<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
	protected ?int $id = null;

    #[ORM\Column(length: 128)]
	#[Assert\NotBlank(message: "Le nom ne peut pas être vide !")]
	#[Assert\Type(type: "string", message: "Le nom doit être une chaîne de caractères !")]
	#[Assert\Length(max: 128, maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $name = null;

    #[ORM\Column(length: 512, nullable: true)]
	#[Assert\Type(type: "string", message: "La description doit être une chaîne de caractères !")]
	#[Assert\Length(max: 512, maxMessage: "La description ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $description = null;

	// Avec cascade, lorsque l'entité Category est persistée, l'entité Media associée le sera aussi
	#[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
	// Ici, le onDelete nullifie la clé étrangère de la table Product lorsqu'une entité Media est supprimée
	#[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
	protected ?Media $media = null;

	// Avec cascade, lorsque l'entité Category est persistée, les entités Product associées le seront aussi
	// remove permet de supprimer les entités Product associées lorsqu'elles ne sont plus associées à une entité Category
	// orphanRemoval permet de supprimer les entités Product associées lorsqu'elles ne sont plus associées à une entité Category
	#[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
	protected Collection $products;

	public function __construct()
	{
		$this->products = new ArrayCollection();
	}
	
	public function getId(): ?int
       {
           return $this->id;
       }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

	public function getMedia(): ?Media
   	{
   		return $this->media;
   	}

	public function setMedia(?Media $media): static
   	{
   		$this->media = $media;
   
   		return $this;
   	}
}