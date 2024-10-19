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
	#[Assert\NotBlank(message: "La quantité ne peut pas être vide !")]
	#[Assert\Type(type: "integer", message: "La quantité doit être un nombre entier !")]
	#[Assert\Positive(message: "La quantité doit être un nombre positif !")]
	protected ?int $quantity = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'cartProductQuantities')]
    #[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "Le produit ne peut pas être vide !")]
	protected ?Product $product = null;

	// persist sert à dire que si on persiste un objet CartProductQuantity, on persiste aussi le panier
    #[ORM\ManyToOne(targetEntity: Cart::class, inversedBy: 'cartProductQuantities', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "Le panier ne peut pas être vide !")]
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
