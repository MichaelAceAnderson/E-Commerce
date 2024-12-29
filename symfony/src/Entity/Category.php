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
	#[Assert\NotBlank(message: "The name cannot be empty!")]
	#[Assert\Type(type: "string", message: "The name must be a string!")]
	#[Assert\Length(max: 128, maxMessage: "The name cannot be longer than {{ limit }} characters!")]
	protected ?string $name = null;

	#[ORM\Column(length: 512, nullable: true)]
	#[Assert\Type(type: "string", message: "The description must be a string!")]
	#[Assert\Length(max: 512, maxMessage: "The description cannot be longer than {{ limit }} characters!")]
	protected ?string $description = null;

	// With cascade, when the Category entity is persisted, the associated Media entity will also be persisted
	#[ORM\ManyToOne(targetEntity: Media::class, cascade: ['persist'])]
	// Here, onDelete nullifies the foreign key in the Product table when a Media entity is deleted
	#[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
	protected ?Media $media = null;

	// With cascade, when the Category entity is persisted, the associated Product entities will also be persisted
	// remove allows deleting associated Product entities when they are no longer associated with a Category entity
	// orphanRemoval allows deleting associated Product entities when they are no longer associated with a Category entity
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