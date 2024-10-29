<?php

namespace App\Entity;

use App\Repository\OrderProductQuantityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderProductQuantityRepository::class)]
class OrderProductQuantity extends AbstractEntity
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank(message: "Quantity cannot be empty!")]
	#[Assert\Type(type: "integer", message: "Quantity must be an integer!")]
	#[Assert\Positive(message: "Quantity must be a positive number!")]
	protected ?int $quantity = null;

	#[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderProductQuantities')]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "Product cannot be empty!")]
	protected ?Product $product = null;

	#[ORM\ManyToOne(targetEntity: UserOrder::class, inversedBy: 'orderProductQuantities', cascade: ['persist'])]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "You must specify the order to which this product quantity applies!")]
	protected ?UserOrder $originalOrder = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getQuantity(): ?int
	{
		return $this->quantity;
	}

	public function setQuantity(?int $quantity): static
	{
		$this->quantity = $quantity;

		return $this;
	}

	public function getProduct(): ?Product
	{
		return $this->product;
	}

	public function setProduct(?Product $product): static
	{
		$this->product = $product;

		return $this;
	}

	public function getOriginalOrder(): ?UserOrder
	{
		return $this->originalOrder;
	}

	public function setOriginalOrder(?UserOrder $originalOrder): static
	{
		$this->originalOrder = $originalOrder;

		return $this;
	}
}
