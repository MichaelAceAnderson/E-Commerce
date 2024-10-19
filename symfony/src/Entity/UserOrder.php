<?php

namespace App\Entity;

use App\Repository\UserOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserOrderRepository::class)]
#[UniqueEntity(fields: ['number'], message: 'Il existe déjà une commande avec ce numéro !')]
# Order étant un mot-clé réservé en SQL, on utilise le nom UserOrder pour éviter les erreurs
class UserOrder extends AbstractEntity
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank(message: "Le numéro de commande ne peut pas être vide !")]
	#[Assert\Type(type: "integer", message: "Le numéro de commande doit être un nombre entier !")]
	#[Assert\Positive(message: "Le numéro de commande doit être un nombre positif !")]
	protected ?int $number = null;

	#[ORM\Column(type: 'boolean', options: ['default' => false], nullable: false)]
	#[Assert\Type(type: "bool", message: "La validation doit être un booléen !")]
	#[Assert\Choice(choices: [true, false], message: "La validation doit être soit vraie, soit fausse !")]
	protected ?bool $isValidated = null;

	#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
	#[Assert\NotBlank(message: "La date de commande ne peut pas être vide !")]
	#[Assert\Type(type: "\DateTimeInterface", message: "La date de commande doit être une date valide !")]
	#[Assert\LessThanOrEqual("today", message: "La date de commande ne peut pas être postérieure à la date du jour !")]
	protected ?\DateTimeInterface $orderDate = null;

	#[ORM\ManyToOne(inversedBy: 'orders')]
	// Lorsqu'un utilisateur est supprimé, il est obligatoire de supprimer ses commandes
	// Pour pouvoir conserver une commande sans utilisateur, on joue sur les contraintes NULL
	// Et on rend la commande détachée et indépendante de l'utilisateur
	#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
	#[Assert\NotBlank(message: "L'utilisateur ne peut pas être vide !")]
	protected ?User $user = null;

	#[ORM\ManyToOne]
	#[ORM\JoinColumn(nullable: false)]
	#[Assert\NotBlank(message: "L'adresse de livraison ne peut pas être vide !")]
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
