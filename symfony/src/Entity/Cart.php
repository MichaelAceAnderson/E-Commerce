<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart extends AbstractEntity
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\OneToMany(targetEntity: CartProductQuantity::class, mappedBy: 'cart', cascade: ['remove'])]
	protected Collection $cartProductQuantities;

	#[ORM\OneToOne(inversedBy: 'cart', cascade: ['persist', 'remove'])]
	#[ORM\JoinColumn(nullable: false)]
	private ?User $user = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getCartProductQuantities()
	{
		return $this->cartProductQuantities;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(User $user): static
	{
		$this->user = $user;

		return $this;
	}
}