<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends AbstractEntityRepository implements PasswordUpgraderInterface
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, User::class);
	}

	/**
	 * Update the user's password hash periodically
	 * 
	 * @param PasswordAuthenticatedUserInterface $user The user to update
	 * @param string $newHashedPassword The new password hash
	 */
	public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
	{
		if (!$user instanceof User) {
			throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported!', $user::class));
		}

		$user->setPassword($newHashedPassword);
		$this->getRepository()->save($user, true);
	}
}