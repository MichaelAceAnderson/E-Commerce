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

	#[Assert\File(maxSize: "8M", maxSizeMessage: "The maximum file size is 8 MB.")]
	// Restrict the type to images and videos only
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
		mimeTypesMessage: "The file must be an image or a video!"
	)]
	protected ?UploadedFile $file = null;

	#[ORM\Column(length: 256, nullable: false)]
	// No NotBlank constraint because this field is processed after form submission
	#[Assert\Type(type: "string", message: "The path must be a string!")]
	protected ?string $path = null;

	#[ORM\Column(length: 256, nullable: true)]
	#[Assert\Length(max: 256, maxMessage: "The alternative text cannot contain more than {{ limit }} characters!")]
	#[Assert\Type(type: "string", message: "The alternative text must be a string!")]
	protected ?string $alt = null;

	#[ORM\ManyToOne(inversedBy: 'medias')]
	#[ORM\JoinColumn(nullable: true)]
	protected ?Product $product = null;

	#[ORM\Column(length: 256, nullable: false)]
	// No NotBlank constraint because this field is processed after form submission
	#[Assert\Type(type: "string", message: "The type must be a string!")]
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
