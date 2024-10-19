<?php

namespace App\Repository;

use App\Entity\AbstractEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AbstractEntity>
 *
 * @method AbstractEntity      insert(array $data) Insère une entité en base de données
 * @method AbstractEntity[]    findByAttributes(array $attribute = [], array $values = [], bool $strict = true) Recherche les entités selon la valeur d'un attribut
 * @method AbstractEntity      save(AbstractEntity $entity, bool $flush = false) Enregistre l'entité en base de données à partir des données de l'objet
 * @method AbstractEntity      delete(AbstractEntity $entity, bool $flush = false) Supprime entité de la base de données à partir des données de l'objet
 * 
 */
abstract class AbstractEntityRepository extends ServiceEntityRepository
{


	/**
	 * Récupérer les attributs d'une entité
	 * 
	 * @param EntityRepository $entityRepository Le repository de l'entité
	 * 
	 * @param bool $getAssociatedEntities Si vrai, récupérer également les attributs qui font référence à d'autres entités
	 * 
	 * @return array Les attributs de l'entité au format ['attribut1', 'attribut2', ...] (ex: ['id', 'name', 'email', ...])
	 */
	public static function getEntityAttributes(EntityRepository $entityRepository, $getAssociatedEntities = true): array
	{
		$metadata = $entityRepository->getEntityClassMetadata();
		$attributes = $getAssociatedEntities ? array_merge($metadata->getFieldNames(), $metadata->getAssociationNames()) : $metadata->getFieldNames();
		return $attributes;
	}

	/**
	 * Récupérer une entité attribut d'une autre entité par son id
	 * 
	 * @param EntityManagerInterface $entityManager Le gestionnaire d'entité
	 * @param string $entityAttribute Le nom de l'entité attribut
	 * @param int $id L'id de l'entité recherchée
	 *  
	 */
	public static function getEntityById(EntityManagerInterface $entityManager, string $entityName, int $id): ?AbstractEntity
	{
		$entityRepository = $entityManager->getRepository('\App\Entity\\' . $entityName);
		$foundEntities = $entityRepository->findByAttributes(['id' => $id]);

		return $foundEntities[0] ?? null;
	}

	/**
	 * Créer le Repository de l'entité
	 * 
	 * @param ManagerRegistry $registry Le service de gestion des entités
	 * @param string $entityClass Le nom de la classe de l'entité à gérer
	 * 
	 * @return ?AbstractEntityRepository Un gestionnaire de requêtes pour l'entité
	 */
	public function __construct(ManagerRegistry $registry, string $entityClass)
	{
		parent::__construct($registry, $entityClass);
	}

	public function getEntityClassMetadata(): \Doctrine\ORM\Mapping\ClassMetadata
	{
		return $this->_em->getClassMetadata($this->_entityName);
	}

	/**
	 * Ajoute une entité en base de données
	 * 
	 * @param array $data Les données à enregistrer
	 * 
	 * @return AbstractEntity L'entité créée
	 */
	public function insert(array $data): AbstractEntity
	{
		$entity = new $this->_entityName($data);
		$this->save($entity, true);

		return $entity;
	}

	/**
	 * Recherche les entités selon des valeurs d'attributs<br>
	 * Note: Cette méthode est conçue pour renvoyer TOUTES les entités qui correspondent aux critères de recherche,
	 * vous devez donc renforcer vos critères ou les contraintes en base de donnéessi vous obtenez trop de résultats 
	 * (ex: plusieurs entités avec le même mail)
	 * 
	 * @param array $attribute Le tableau associatif d'attributs à chercher (ex: ['name' => 'John', 'email' => 'john@doe.com'])<br>
	 * @param bool $strict Indique si la recherche prend ce qui contient ou ce qui vaut exactement les valeurs recherchées
	 * @param string $orderBy L'attribut par lequel trier les résultats et l'ordre de tri (Ex: ['id', 'ASC'] ou ['name', 'DESC'], ...), par défaut, tri par id croissant
	 * 
	 * @return AbstractEntity[] Un tableau d'entités
	 */
	public function findByAttributes(array $attributes = [], bool $strict = true, array $orderBy = null): array
	{
		// Filtrer les attributs pour ne garder ceux qui existent dans l'entité
		$attributes = array_filter($attributes, function ($attribute) {
			return in_array($attribute, $this->getEntityAttributes($this), true);
		}, ARRAY_FILTER_USE_KEY);

		// Récupérer uniquement les attributs qui sont des associations
		// et font référence à d'autres entités
		$associationAttributes = array_diff($this->getEntityAttributes($this, true), $this->getEntityAttributes($this, false));

		$queryBuilder = $this->createQueryBuilder('entity');
		foreach ($attributes as $attribute => $value) {
			if (!$strict) {
				// Si l'attribut est une association, on fait une jointure sans utiliser LIKE
				if (in_array($attribute, $associationAttributes)) {
					$queryBuilder
						->join('entity.' . $attribute, $attribute)
						->orWhere($attribute . '.id = :' . $attribute . 'Value')
						->setParameter($attribute . 'Value', $value);
					continue;
				} else {
					$queryBuilder
						->orWhere('entity.' . $attribute . ' LIKE :' . $attribute . 'Value')
						->setParameter($attribute . 'Value', '%' . $value . '%');
				}
				
			} else {
				// Si l'attribut est une association, on fait une jointure
				if (in_array($attribute, $associationAttributes)) {
					$queryBuilder
						->join('entity.' . $attribute, $attribute);
				}
				$queryBuilder
					->andWhere('entity.' . $attribute . ' = :' . $attribute . 'Value')
					->setParameter($attribute . 'Value', $value);
			}
		}

		if (!is_null($orderBy)) {
			$queryBuilder->orderBy('entity.' . $orderBy[0] ?? 'id', $orderBy[1] ?? 'ASC');
		}

		return $queryBuilder->getQuery()->getResult();
	}

	/**
	 * Enregistre l'entité en base de données
	 * 
	 * @param AbstractEntity $entity L'entité à enregistrer
	 * @param bool $flush Indique si la requête doit être exécutée immédiatement
	 * 
	 * @return AbstractEntity L'entité enregistrée
	 */
	public function save(AbstractEntity $entity, bool $flush = false): AbstractEntity
	{
		$this->getEntityManager()->persist($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}

		return $entity;
	}

	/**
	 * Supprime l'entité de la base de données
	 * 
	 * @param AbstractEntity $entity L'entité à supprimer
	 * @param bool $flush Indique si la requête doit être exécutée immédiatement
	 * 
	 * @return AbstractEntity
	 */
	public function delete(AbstractEntity $entity, bool $flush = false): AbstractEntity
	{
		$this->getEntityManager()->remove($entity);

		if ($flush) {
			$this->getEntityManager()->flush();
		}

		return $entity;
	}
}