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
	 * Determine the delivery cost of an order from a total price
	 * 
	 * @param string total price of the order
	 * 
	 * @return string The delivery cost
	 */
	public function getDeliveryCost(string $totalPrice): string
	{
		// If the total is greater than 30€, delivery is free
		$totalPrice < 30 ? $deliveryFee = '5.99' : $deliveryFee = '0.00';
		
		return $deliveryFee;
	}
}
