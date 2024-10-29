<?php

namespace App\Entity;

use App\Repository\CartProductQuantityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CartProductQuantityRepository::class)]
class CartProductQuantity extends AbstractEntity
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\Column]
	#[Assert\NotBlank(message: "Quantity cannot be empty!")]
	#[Assert\Type(type: "integer", message: "Quantity must be an integer!")]
	#[Assert\Positive(message: "Quantity must be a positive number!")]
	protected ?int $quantity = null;

	#[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'cartProductQuantities')]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "Product cannot be empty!")]
	protected ?Product $product = null;

	// persist means that if a CartProductQuantity object is persisted, the cart is also persisted
	#[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'cartProductQuantities', cascade: ['persist'])]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "Cart cannot be empty!")]
	protected ?Cart $cart = null;

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

	public function getCart(): ?Cart
	{
		return $this->cart;
	}

	public function setCart(?Cart $cart): static
	{
		$this->cart = $cart;

		return $this;
	}
}
