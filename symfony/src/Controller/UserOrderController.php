<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProductQuantity;
use App\Entity\CustomerAddress;
use App\Entity\UserOrder;
use App\Entity\OrderProductQuantity;
use App\Form\CustomerAddressType;
use App\Repository\UserOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/order', name: 'app_order_')]
class UserOrderController extends AbstractEntityController
{
	/**
	 * Create the entity controller
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager service
	 * @param UserOrderRepository  $entityRepository The entity repository
	 * 
	 * @return static An entity controller
	 */
	public function __construct(EntityManagerInterface $entityManager, UserOrderRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * This route displays the user's orders page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	public function index(Request $request): Response
	{
		// List the user's orders
		$orders = $this->getUser()->getOrders();

		return $this->render('pages/order/list.html.twig', [
			'orders' => $orders,
		]);
	}

	/**
	 * This route displays a specific order
	 * 
	 * @param int $id The order ID
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(int $id): Response
	{
		// Retrieve the order
		$order = $this->getRepository()->find($id);

		// If the order does not exist, return an error
		if (is_null($order)) {
			$this->addFlash('error', 'The requested order does not exist!');
			return $this->redirectToRoute('app_order_list');
		}

		// Check that the order belongs to the user
		if ($order->getUser() !== $this->getUser()) {
			$this->addFlash('error', 'You are not allowed to view this order!');
			return $this->redirectToRoute('app_order_list');
		}

		// Retrieve the order lines
		$orderProductQuantities = $order->getOrderProductQuantities();

		return $this->render('pages/order/show.html.twig', [
			'order' => $order,
			'orderProductQuantities' => $orderProductQuantities
		]);
	}

	/**
	 * This route creates an order from the order creation form information
	 * 
	 * @param Request $request The HTTP request
	 * @param int $cartId The cart ID
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/create/{cartId}', name: 'create', methods: ['GET', 'POST'])]
	public function create(Request $request, int $cartId): Response
	{
		// Retrieve the URL of the page the user came from
		$lastPage = $request->headers->get('referer');

		// If the user is not logged in, prevent the action
		if (!$this->getUser()) {
			$this->addFlash('error', 'You must be logged in to place an order!');
			// Redirect the user to the page they came from or to the login page
			return $lastPage ? $this->redirect($lastPage) : $this->redirectToRoute('app_user_login');
		}

		// Retrieve the cart
		$cart = $this->getUser()->getCart();

		// If the cart does not exist, create it
		if (is_null($cart)) {
			try {
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while creating the cart!');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Create the delivery address form
		$addressForm = $this->createForm(CustomerAddressType::class);

		// Process the form
		$addressForm->handleRequest($request);

		if ($addressForm->isSubmitted() && $addressForm->isValid()) {
			// Retrieve the form data
			$address = $addressForm->getData();

			try {
				// To avoid creating orders despite errors, use a transaction
				$this->getEntityManager()->beginTransaction();

				// Save the address
				$address->setUser($this->getUser());
				$this->getEntityManager()->getRepository(CustomerAddress::class)->save($address, true);

				$cartItems = $this->getEntityManager()->getRepository(CartProductQuantity::class)->findByAttributes(['cart' => $cart]);
				$totalPrice = 0.00;
				foreach ($cartItems as $cartItem) {
					$totalPrice += ($cartItem->getProduct()->getPrice() * $cartItem->getQuantity());
				}
				$deliveryCost = $this->getEntityManager()->getRepository(Cart::class)->getDeliveryCost($totalPrice);

				// Create the order
				$orderCount = $this->getRepository()->count(['user' => $this->getUser()]);
				$order = new UserOrder([
					'number' => intval($cart->getUser()->getId() . $cart->getUser()->getCart()->getId() . $orderCount),
					'user' => $cart->getUser(),
					'isValidated' => false,
					'orderDate' => new \DateTime(),
					'customerAddress' => $address,
					'deliveryFee' => $deliveryCost,
				]);

				// Create the order lines
				$orderItems = [];
				foreach ($cartItems as $cartItem) {
					$orderItems[] = new OrderProductQuantity([
						'product' => $cartItem->getProduct(),
						'quantity' => $cartItem->getQuantity(),
						'originalOrder' => $order,
					]);
				}

				// Empty the cart
				foreach ($cartItems as $cartItem) {
					$this->getEntityManager()->getRepository(CartProductQuantity::class)->delete($cartItem, true);
				}

				// Save the order
				$this->getRepository()->save($order, true);
				// Save the order lines
				foreach ($orderItems as $orderItem) {
					$this->getEntityManager()->getRepository(OrderProductQuantity::class)->save($orderItem, true);
				}

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Your order (No. ' . $order->getNumber() . ') has been successfully created!');
				return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while creating the order!');
			}
		}

		return $this->render('pages/order/create.html.twig', [
			'cart' => $cart,
			'addressForm' => $addressForm->createView(),
		]);
	}
}
