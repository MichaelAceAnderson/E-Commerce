<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product extends AbstractEntity
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\Column(length: 256, nullable: false)]
	#[Assert\NotBlank(message: "Le nom du produit ne peut pas être vide !")]
	#[Assert\Type(type: "string", message: "Le nom du produit doit être une chaîne de caractères !")]
	#[Assert\Length(max: 256, maxMessage: "Le nom du produit ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $name = null;

	#[ORM\Column(length: 512, nullable: true)]
	#[Assert\Type(type: "string", message: "La description du produit doit être une chaîne de caractères !")]
	#[Assert\Length(max: 512, maxMessage: "La description du produit ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $description = null;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank(message: "Le prix ne peut pas être vide !")]
	#[Assert\Type(type: "float", message: "Le prix doit être un nombre décimal !")]
	#[Assert\Positive(message: "Le prix doit être un nombre positif !")]
	protected ?float $price = null;

	#[ORM\Column(type: 'boolean', options: ['default' => false], nullable: false)]
	#[Assert\Type(type: "bool", message: "La disponibilité doit être un booléen !")]
	#[Assert\Choice(choices: [true, false], message: "La disponibilité doit être soit vrai, soit faux !")]
	protected ?bool $available = null;

	#[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "La catégorie ne peut pas être vide !")]
	protected ?Category $category = null;

	// persist permet de sauvegarder les médias en même temps que le produit
	#[ORM\OneToMany(mappedBy: 'product', targetEntity: Media::class, cascade: ['persist'])]
	protected Collection $medias;

	#[ORM\OneToMany(targetEntity: OrderProductQuantity::class, mappedBy: 'product', cascade: ['remove'])]
    protected Collection $orderProductQuantities;

	#[ORM\OneToMany(targetEntity: CartProductQuantity::class, mappedBy: 'product', cascade: ['remove'])]
	protected Collection $cartProductQuantities;


	public function __construct()
	{
		$this->medias = new ArrayCollection();
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

	public function getPrice(): ?float
	{
		return $this->price;
	}

	public function setPrice(?float $price): static
	{
		$this->price = $price;

		return $this;
	}

	public function isAvailable(): ?bool
	{
		return $this->available;
	}

	public function setAvailable(?bool $available): static
	{
		$this->available = $available;

		return $this;
	}

	public function getCategory(): ?Category
	{
		return $this->category;
	}

	public function setCategory(?Category $category): static
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * @return Collection<int, Media>
	 */
	public function getMedias(): Collection
	{
		return $this->medias;
	}

	public function addMedia(Media $media): static
	{
		if (!$this->medias->contains($media)) {
			$this->medias->add($media);
			$media->setProduct($this);
		}

		return $this;
	}

	public function removeMedia(Media $media): static
	{
		if ($this->medias->removeElement($media)) {
			// set the owning side to null (unless already changed)
			if ($media->getProduct() === $this) {
				$media->setProduct(null);
			}
		}

		return $this;
	}
}