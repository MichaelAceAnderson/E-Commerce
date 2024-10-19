<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @method static AbstractEntityController setEntityManager(EntityManagerInterface $entityManager) Définir le service de gestion des entités
 * @method static EntityManagerInterface getEntityManager() Récupérer le service de gestion des entités
 * @method static EntityRepository setRepository(EntityRepository $repository) Définir le repository de l'entité
 * @method static EntityRepository getRepository() Récupérer le repository de l'entité
 */
abstract class AbstractEntityController extends AbstractController
{
    private EntityRepository $repository;
	private EntityManagerInterface $entityManager;

	/**
	 * Créer le contrôleur de l'entité
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * @param EntityRepository $entityRepository Le repository de l'entité
	 * 
	 * @return AbstractEntityController Un contrôleur pour l'entité
	 */
	public function __construct(EntityManagerInterface $entityManager, EntityRepository $entityRepository)
	{
		$this->setEntityManager($entityManager);
		$this->setRepository($entityRepository);
	}

	/**
	 * Définir le service de gestion des entités
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * 
	 * @return AbstractEntityController Le contrôleur de l'entité
	 */
	public function setEntityManager(EntityManagerInterface $entityManager): static
	{
		$this->entityManager = $entityManager;
		return $this;
	}
	
	/**
	 * Récupérer le service de gestion des entités
	 * 
	 * @return EntityManagerInterface Le service de gestion des entités
	 */
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}

	/**
	 * Définir le repository de l'entité
	 * 
	 * @param EntityRepository $repository Le repository de l'entité
	 * 
	 * @return AbstractEntityController Le contrôleur de l'entité
	 */
	public function setRepository(EntityRepository $repository): static
	{
		$this->repository = $repository;
		return $this;
	}

	/**
	 * Récupérer le repository de l'entité
	 * 
	 * @return EntityRepository Le repository de l'entité
	 */
	public function getRepository(): EntityRepository
	{
		return $this->repository;
	}
}