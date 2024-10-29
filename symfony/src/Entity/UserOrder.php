<?php

namespace App\Entity;

use App\Repository\UserOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserOrderRepository::class)]
#[UniqueEntity(fields: ['number'], message: 'There is already an order with this number!')]
// Order is a reserved keyword in SQL, so we use the name UserOrder to avoid errors
class UserOrder extends AbstractEntity
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank(message: "The order number cannot be empty!")]
	#[Assert\Type(type: "integer", message: "The order number must be an integer!")]
	#[Assert\Positive(message: "The order number must be a positive number!")]
	protected ?int $number = null;

	#[ORM\Column(type: 'boolean', options: ['default' => false], nullable: false)]
	#[Assert\Type(type: "bool", message: "Validation must be a boolean!")]
	#[Assert\Choice(choices: [true, false], message: "Validation must be either true or false!")]
	protected ?bool $isValidated = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
	#[Assert\NotBlank(message: "The order date cannot be empty!")]
	#[Assert\Type(type: "\DateTimeInterface", message: "The order date must be a valid date!")]
	#[Assert\LessThanOrEqual("today", message: "The order date cannot be later than today!")]
	protected ?\DateTimeInterface $orderDate = null;

	#[ORM\ManyToOne(inversedBy: 'orders')]
	// When a user is deleted, it is mandatory to delete their orders
	// To be able to keep an order without a user, we play with NULL constraints
	// And make the order detached and independent of the user
	#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
	#[Assert\NotBlank(message: "The user cannot be empty!")]
	protected ?User $user = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "The delivery address cannot be empty!")]
	protected ?CustomerAddress $customerAddress = null;

	#[ORM\OneToMany(targetEntity: OrderProductQuantity::class, mappedBy: 'originalOrder', cascade: ['remove'])]
	protected $orderProductQuantities;

	#[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
	private ?string $deliveryFee = null;

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getNumber(): ?int
	{
		return $this->number;
	}

	public function setNumber(?int $number): static
	{
		$this->number = $number;

		return $this;
	}

	public function isValidated(): ?bool
	{
		return $this->isValidated;
	}

	public function setIsValidated(?bool $isValidated): static
	{
		$this->isValidated = $isValidated;

		return $this;
	}

	public function getOrderDate(): ?\DateTimeInterface
	{
		return $this->orderDate;
	}

	public function setOrderDate(?\DateTimeInterface $orderDate): static
	{
		$this->orderDate = $orderDate;

		return $this;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}

	public function setUser(?User $user): static
	{
		$this->user = $user;

		return $this;
	}

	public function getCustomerAddress(): ?CustomerAddress
	{
		return $this->customerAddress;
	}

	public function setCustomerAddress(?CustomerAddress $customerAddress): static
	{
		$this->customerAddress = $customerAddress;

		return $this;
	}

	public function getOrderProductQuantities()
	{
		return $this->orderProductQuantities;
	}

	public function getDeliveryFee(): ?string
	{
		return $this->deliveryFee;
	}

	public function setDeliveryFee(string $deliveryFee): static
	{
		$this->deliveryFee = $deliveryFee;

		return $this;
	}
}
