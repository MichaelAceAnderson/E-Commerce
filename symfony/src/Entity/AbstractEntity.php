<?php

namespace App\Entity;
use Doctrine\ORM\PersistentCollection;

abstract class AbstractEntity
{
	/**
	 * Créer une entité
	 * 
	 * @param array $data Les données de l'entité
	 */
	public function __construct(array $data = [])
	{
		$this->hydrate($data);
	}

	/**
	 * Hydrate l'objet avec les données passées en paramètre
	 * 
	 * @param array $data Les données à hydrater
	 * 
	 * @return bool true si toutes les données ont été prises en compte correctement, false sinon
	 * 
	 * @throws \InvalidArgumentException Si l'attribut n'existe pas
	 * 
	 */
	public function hydrate(array $data): bool
	{
		foreach ($data as $key => $value) {
			$method = 'set' . ucfirst($key);

			if (!method_exists($this, $method)) {
				throw new \InvalidArgumentException('La méthode ' . $method . ' n\'existe pas dans ' . $this::class . ' et ne peut donc pas traiter l\'attribut ' . $key . ' !');
			} else {
				$this->$method($value);
			}
		}

		return true;
	}

	/**
	 * Récupère l'id de l'entité
	 * 
	 * @return int|null L'id de l'entité
	 */
	public abstract function getId(): ?int;

	/**
	 * Affiche l'entité en chaîne de caractères
	 * 
	 * @return string L'entité sous forme de chaîne de caractères
	 */
	public function __toString(): string
	{
		return json_encode($this->toArray());
	}


	/**
	 * Convertit l'entité en tableau.
	 * Note: Cette méthode est nécessaire pour récupérer le tableau de variables en dehors de cette classe à cause de l'encapsulation
	 * 
	 * @return array L'entité sous forme de tableau
	 */
	public function toArray(): array
	{
		$entityArray = get_object_vars($this);
		// Convertir les sous-entités en id et les collections en tableau
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