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
	 * Mettre à jour le hash du mot de passe de l'utilisateur périodiquement
	 * 
	 * @param PasswordAuthenticatedUserInterface $user L'utilisateur à mettre à jour // Add this line
	 * @param string $newHashedPassword Le nouveau hash du mot de passe
	 */
	public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
	{
		if (!$user instanceof User) {
			throw new UnsupportedUserException(sprintf('La manipulation d\'instances de "%s" n\'est pas supportée !', $user::class));
		}

		$user->setPassword($newHashedPassword);
		$this->getRepository()->save($user, true);
	}
}
