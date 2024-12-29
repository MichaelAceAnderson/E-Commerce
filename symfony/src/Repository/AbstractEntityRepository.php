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
 * @method AbstractEntity      insert(array $data) Inserts an entity into the database
 * @method AbstractEntity[]    findByAttributes(array $attribute = [], array $values = [], bool $strict = true) Searches entities by attribute values
 * @method AbstractEntity      save(AbstractEntity $entity, bool $flush = false) Saves the entity to the database from the object data
 * @method AbstractEntity      delete(AbstractEntity $entity, bool $flush = false) Deletes the entity from the database from the object data
 * 
 */
abstract class AbstractEntityRepository extends ServiceEntityRepository
{


	/**
	 * Retrieve the attributes of an entity
	 * 
	 * @param EntityRepository $entityRepository The entity repository
	 * 
	 * @param bool $getAssociatedEntities If true, also retrieve attributes that reference other entities
	 * 
	 * @return array The attributes of the entity in the format ['attribute1', 'attribute2', ...] (e.g., ['id', 'name', 'email', ...])
	 */
	public static function getEntityAttributes(EntityRepository $entityRepository, $getAssociatedEntities = true): array
	{
		$metadata = $entityRepository->getEntityClassMetadata();
		$attributes = $getAssociatedEntities ? array_merge($metadata->getFieldNames(), $metadata->getAssociationNames()) : $metadata->getFieldNames();
		return $attributes;
	}

	/**
	 * Retrieve an entity attribute of another entity by its id
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager
	 * @param string $entityAttribute The name of the entity attribute
	 * @param int $id The id of the searched entity
	 *  
	 */
	public static function getEntityById(EntityManagerInterface $entityManager, string $entityName, int $id): ?AbstractEntity
	{
		$entityRepository = $entityManager->getRepository('\App\Entity\\' . $entityName);
		$foundEntities = $entityRepository->findByAttributes(['id' => $id]);

		return $foundEntities[0] ?? null;
	}

	/**
	 * Create the entity repository
	 * 
	 * @param ManagerRegistry $registry The entity management service
	 * @param string $entityClass The name of the entity class to manage
	 * 
	 * @return ?AbstractEntityRepository A query manager for the entity
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
	 * Adds an entity to the database
	 * 
	 * @param array $data The data to save
	 * 
	 * @return AbstractEntity The created entity
	 */
	public function insert(array $data): AbstractEntity
	{
		$entity = new $this->_entityName($data);
		$this->save($entity, true);

		return $entity;
	}

	/**
	 * Searches entities by attribute values<br>
	 * Note: This method is designed to return ALL entities that match the search criteria,
	 * so you should strengthen your criteria or database constraints if you get too many results 
	 * (e.g., multiple entities with the same email)
	 * 
	 * @param array $attribute The associative array of attributes to search (e.g., ['name' => 'John', 'email' => 'john@doe.com'])<br>
	 * @param bool $strict Indicates whether the search takes what contains or exactly matches the searched values
	 * @param string $orderBy The attribute by which to sort the results and the sort order (e.g., ['id', 'ASC'] or ['name', 'DESC'], ...), by default, sort by ascending id
	 * 
	 * @return AbstractEntity[] An array of entities
	 */
	public function findByAttributes(array $attributes = [], bool $strict = true, array $orderBy = null): array
	{
		// Filter attributes to keep only those that exist in the entity
		$attributes = array_filter($attributes, function ($attribute) {
			return in_array($attribute, $this->getEntityAttributes($this), true);
		}, ARRAY_FILTER_USE_KEY);

		// Retrieve only attributes that are associations
		// and reference other entities
		$associationAttributes = array_diff($this->getEntityAttributes($this, true), $this->getEntityAttributes($this, false));

		$queryBuilder = $this->createQueryBuilder('entity');
		foreach ($attributes as $attribute => $value) {
			if (!$strict) {
				// If the attribute is an association, we make a join without using LIKE
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
				// If the attribute is an association, we make a join
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
	 * Saves the entity to the database
	 * 
	 * @param AbstractEntity $entity The entity to save
	 * @param bool $flush Indicates whether the query should be executed immediately
	 * 
	 * @return AbstractEntity The saved entity
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
	 * Deletes the entity from the database
	 * 
	 * @param AbstractEntity $entity The entity to delete
	 * @param bool $flush Indicates whether the query should be executed immediately
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