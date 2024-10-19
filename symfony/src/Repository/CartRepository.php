<?php

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends AbstractEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

	/**
	 * Déterminer le prix de livraison d'une commande à partir d'un tableau de produits
	 * 
	 * @param string prix total de la commande
	 * 
	 * @return string Le prix de livraison
	 */
	public function getDeliveryCost(string $totalPrice): string
	{
		// Si le total est supérieur à 30€, la livraison est offerte
		$totalPrice < 30 ? $deliveryFee = '5.99' : $deliveryFee = '0.00';
		
		return $deliveryFee;
	}
}
