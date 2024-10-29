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
	#[Assert\NotBlank(message: "The product name cannot be empty!")]
	#[Assert\Type(type: "string", message: "The product name must be a string!")]
	#[Assert\Length(max: 256, maxMessage: "The product name cannot be longer than {{ limit }} characters!")]
	protected ?string $name = null;

	#[ORM\Column(length: 512, nullable: true)]
	#[Assert\Type(type: "string", message: "The product description must be a string!")]
	#[Assert\Length(max: 512, maxMessage: "The product description cannot be longer than {{ limit }} characters!")]
	protected ?string $description = null;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank(message: "The price cannot be empty!")]
	#[Assert\Type(type: "float", message: "The price must be a decimal number!")]
	#[Assert\Positive(message: "The price must be a positive number!")]
	protected ?float $price = null;

	#[ORM\Column(type: 'boolean', options: ['default' => false], nullable: false)]
	#[Assert\Type(type: "bool", message: "Availability must be a boolean!")]
	#[Assert\Choice(choices: [true, false], message: "Availability must be either true or false!")]
	protected ?bool $available = null;

	#[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'products')]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "The category cannot be empty!")]
	protected ?Category $category = null;

	// persist allows saving media along with the product
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
