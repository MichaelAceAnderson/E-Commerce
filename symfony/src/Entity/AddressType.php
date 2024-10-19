<?php

namespace App\Entity;

use App\Repository\AddressTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AddressTypeRepository::class)]
class AddressType extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
	protected ?int $id = null;

    #[ORM\Column(length: 64, nullable: false)]
	#[Assert\NotBlank(message: "Le type d'adresse ne peut pas être vide !")]
	#[Assert\Length(max: 64, maxMessage: "Le type d'adresse ne peut pas contenir plus de {{ limit }} caractères !")]
	protected ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}