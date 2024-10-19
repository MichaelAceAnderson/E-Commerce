<?php

namespace App\Entity;

use App\Repository\CustomerAddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerAddressRepository::class)]
class CustomerAddress extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
	protected ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
	protected ?AddressType $type = null;

    #[ORM\Column(length: 128)]
	#[Assert\Length(max: 128, maxMessage: "Le nom ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $lastName = null;

    #[ORM\Column(length: 128)]
	#[Assert\Length(max: 128, maxMessage: "Le prénom ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $firstName = null;

    #[ORM\Column(length: 12)]
	#[Assert\NotBlank(message: "Le numéro de téléphone ne peut pas être vide !")]
	#[Assert\Type(type: "string", message: "Le numéro de téléphone doit être une chaîne de caractères !")]
	#[Assert\Length(max: 12, maxMessage: "Le numéro de téléphone ne peut pas contenir plus de {{ limit }} caractères !")]
	#[Assert\Regex(pattern: "/^\+?\d{2,12}$/", message: "Le numéro de téléphone ne peut contenir que des chiffres et un indicateur régional (+33, +41, etc.) !")]
	protected ?string $phone = null;

    #[ORM\Column(length: 512)]
	#[Assert\Length(max: 512, maxMessage: "L'adresse ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $address = null;

    #[ORM\Column(length: 7)]
	#[Assert\Regex(pattern: "/^[a-zA-Z0-9]{2,7}$/", message: "Le code postal ne peut contenir que 2 à 7 caractères alphanumériques !")]
	protected ?string $postalCode = null;

    #[ORM\Column(length: 256)]
	#[Assert\Length(max: 256, maxMessage: "La ville ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $city = null;

    #[ORM\Column(length: 64)]
	#[Assert\Length(max: 64, maxMessage: "Le pays ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $country = null;

	// Cascade permet de supprimer les paniers d'un utilisateur lorsqu'il est supprimé
    #[ORM\ManyToOne(inversedBy: 'customerAddresses')]
    #[ORM\JoinColumn(nullable: false)]
	protected ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?AddressType
    {
        return $this->type;
    }

    public function setType(?AddressType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(?int $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

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
}
