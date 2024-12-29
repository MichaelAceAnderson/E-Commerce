<?php

namespace App\Entity;
use Doctrine\ORM\PersistentCollection;

abstract class AbstractEntity
{
	/**
	 * Create an entity
	 * 
	 * @param array $data The entity data
	 */
	public function __construct(array $data = [])
	{
		$this->hydrate($data);
	}

	/**
	 * Hydrate the object with the given data
	 * 
	 * @param array $data The data to hydrate
	 * 
	 * @return bool true if all data has been correctly processed, false otherwise
	 * 
	 * @throws \InvalidArgumentException If the attribute does not exist
	 * 
	 */
	public function hydrate(array $data): bool
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst($key);

			if (!method_exists($this, $method)) {
				throw new \InvalidArgumentException('The method ' . $method . ' does not exist in ' . $this::class . ' and therefore cannot process the attribute ' . $key . '!');
			} else {
				$this->$method($value);
			}
		}

		return true;
	}

	/**
	 * Get the entity id
	 * 
	 * @return int|null The entity id
	 */
	public abstract function getId(): ?int;

	/**
	 * Display the entity as a string
	 * 
	 * @return string The entity as a string
	 */
	public function __toString(): string
	{
		return json_encode($this->toArray());
	}


	/**
	 * Convert the entity to an array.
	 * Note: This method is necessary to retrieve the array of variables outside of this class due to encapsulation
	 * 
	 * @return array The entity as an array
	 */
	public function toArray(): array
	{
		$entityArray = get_object_vars($this);
		// Convert sub-entities to id and collections to array
		foreach ($entityArray as $key => $value) {
			if ($value instanceof self) {
				$entityArray[$key] = $value->getId();
			}
			else if ($value instanceof PersistentCollection) {
				$entityArray[$key] = $value->toArray();
			}
			else if (is_array($value)) {
				$entityArray[$key] = json_encode($value);
			}
		}

		return $entityArray;
	}
}