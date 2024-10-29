<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email!')]
class User extends AbstractEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
	#[ORM\Id]
	#[ORM\GeneratedValue]
	#[ORM\Column]
	protected ?int $id = null;

	#[ORM\Column(length: 128, nullable: false)]
	#[Assert\NotBlank(message: "The last name cannot be empty!")]
	#[Assert\Type(type: "string", message: "The last name must be a string!")]
	#[Assert\Length(max: 128, maxMessage: "The last name cannot be longer than {{ limit }} characters!")]
	protected ?string $lastName = null;

	#[ORM\Column(length: 128, nullable: false)]
	#[Assert\NotBlank(message: "The first name cannot be empty!")]
	#[Assert\Type(type: "string", message: "The first name must be a string!")]
	#[Assert\Length(max: 128, maxMessage: "The first name cannot be longer than {{ limit }} characters!")]
	protected ?string $firstName = null;

	#[ORM\Column(length: 12, nullable: false)]
	#[Assert\NotBlank(message: "The phone number cannot be empty!")]
	#[Assert\Type(type: "string", message: "The phone number must be a string!")]
	#[Assert\Length(max: 12, maxMessage: "The phone number cannot be longer than {{ limit }} characters!")]
	#[Assert\Regex(pattern: "/^\+?\d{2,12}$/", message: "The phone number can only contain digits and a regional indicator (+33, +41, etc.)!")]
	protected ?string $phone = null;

	#[ORM\Column(length: 180, unique: true, nullable: false)]
	#[Assert\NotBlank(message: "The email cannot be empty!")]
	#[Assert\Type(type: "string", message: "The email must be a string!")]
	#[Assert\Email(message: "The address {{ value }} is not a valid email address!")]
	#[Assert\Length(max: 180, maxMessage: "The email cannot be longer than {{ limit }} characters!")]
	protected ?string $email = null;

	#[ORM\Column(nullable: false)]
	#[Assert\NotBlank(message: "The password cannot be empty!")]
	#[Assert\Type(type: "string", message: "The password must be a string!")]
	#[Assert\Length(min: 6, minMessage: "The password must be at least {{ limit }} characters long!")]
	protected ?string $password = null;

	#[ORM\Column(type: 'boolean', options: ['default' => false], nullable: false)]
	#[Assert\Type(type: "bool", message: "The verification must be a boolean!")]
	#[Assert\Choice(choices: [true, false], message: "The verification must be either true or false!")]
	protected bool $isVerified = false;

	#[ORM\Column]
	protected array $roles = [];

	#[ORM\OneToMany(mappedBy: 'user', targetEntity: UserOrder::class)]
	protected Collection $orders;

	#[ORM\OneToMany(mappedBy: 'user', targetEntity: CustomerAddress::class, orphanRemoval: true)]
	protected Collection $customerAddresses;

	#[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
	private ?Cart $cart = null;

	public function __construct(array $data = [])
	{
		$this->orders = new ArrayCollection();
		$this->customerAddresses = new ArrayCollection();

		$this->hydrate($data);
	}

	public function getId(): ?int
	{
		return $this->id;
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

	public function getEmail(): ?string
	{
		return $this->email;
	}

	public function setEmail(?string $email): static
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUserIdentifier(): string
	{
		return (string) $this->email;
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array
	{
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}

	public function setRoles(?array $roles): static
	{
		$this->roles = $roles;

		return $this;
	}

	public function addRole(string $role): static
	{
		$this->roles[] = $role;

		return $this;
	}

	public function removeRole(string $role): static
	{
		$key = array_search($role, $this->roles);
		if ($key !== false) {
			unset($this->roles[$key]);
		}

		return $this;
	}

	/**
	 * @see PasswordAuthenticatedUserInterface
	 */
	public function getPassword(): string
	{
		return $this->password;
	}

	public function setPassword(?string $password): static
	{
		$this->password = $password;

		return $this;
	}

	/**
	 * @return Collection<int, UserOrder>
	 */
	public function getOrders(): Collection
	{
		return $this->orders;
	}

	public function addOrder(UserOrder $order): static
	{
		if (!$this->orders->contains($order)) {
			$this->orders->add($order);
			$order->setUser($this);
		}

		return $this;
	}

	public function removeOrder(UserOrder $order): static
	{
		if ($this->orders->removeElement($order)) {
			// set the owning side to null (unless already changed)
			if ($order->getUser() === $this) {
				$order->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection<int, CustomerAddress>
	 */
	public function getCustomerAddresses(): Collection
	{
		return $this->customerAddresses;
	}

	public function addCustomerAddress(?CustomerAddress $customerAddress): static
	{
		if (!$this->customerAddresses->contains($customerAddress)) {
			$this->customerAddresses->add($customerAddress);
			$customerAddress->setUser($this);
		}

		return $this;
	}

	public function removeCustomerAddress(?CustomerAddress $customerAddress): static
	{
		if ($this->customerAddresses->removeElement($customerAddress)) {
			// set the owning side to null (unless already changed)
			if ($customerAddress->getUser() === $this) {
				$customerAddress->setUser(null);
			}
		}

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials(): void
	{
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->password = null;
	}

	public function isVerified(): bool
	{
		return $this->isVerified;
	}

	public function setIsVerified(?bool $isVerified): static
	{
		$this->isVerified = $isVerified;

		return $this;
	}

	public function getCart(): ?Cart
	{
		return $this->cart;
	}

	public function setCart(Cart $cart): static
	{
		// set the owning side of the relation if necessary
		if ($cart->getUser() !== $this) {
			$cart->setUser($this);
		}

		$this->cart = $cart;

		return $this;
	}
}