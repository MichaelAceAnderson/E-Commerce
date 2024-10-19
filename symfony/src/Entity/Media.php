<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media extends AbstractEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
	protected ?int $id = null;

	#[Assert\File(maxSize: "8M", maxSizeMessage: "La taille maximale du fichier est de 8 Mo.")]
	// Restreindre le type aux images et vidéos uniquement
	#[Assert\File(
		mimeTypes: [
			"image/jpeg",
			"image/png",
			"image/gif",
			"image/svg+xml",
			"image/webp",
			"video/mp4",
			"video/flv",
			"video/avi",
			"video/wmv",
			"video/mov",
			"video/mpg",
			"video/mpeg",
			"video/mkv",
			"video/3gp",
			"video/webm",
		],
		mimeTypesMessage: "Le fichier doit être une image ou une vidéo !"
	)]
	protected ?UploadedFile $file = null;

    #[ORM\Column(length: 256, nullable: false)]
	// On ne met pas de contrainte NotBlank car ce champ est traité après soumission du formulaire
	#[Assert\Type(type: "string", message: "Le chemin doit être une chaîne de caractères !")]
	protected ?string $path = null;

    #[ORM\Column(length: 256, nullable: true)]
	#[Assert\Length(max: 256, maxMessage: "Le texte alternatif ne peut pas contenir plus de {{ limit }} caractères !")]
	#[Assert\Type(type: "string", message: "Le texte alternatif doit être une chaîne de caractères !")]
	protected ?string $alt = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
	#[ORM\JoinColumn(nullable: true)]
	protected ?Product $product = null;

	#[ORM\Column(length: 256, nullable: false)]
	// On ne met pas de contrainte NotBlank car ce champ est traité après soumission du formulaire
	#[Assert\Type(type: "string", message: "Le type doit être une chaîne de caractères !")]
	protected ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    public function setFile(?UploadedFile $file): static
    {
        $this->file = $file;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(?string $alt): static
    {
        $this->alt = $alt;

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
