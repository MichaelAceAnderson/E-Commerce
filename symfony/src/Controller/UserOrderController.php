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
	 * Créer le contrôleur de l'entité
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * @param UserOrderRepository  $entityRepository Le repository de l'entité
	 * 
	 * @return static Un contrôleur pour l'entité
	 */
	public function __construct(EntityManagerInterface $entityManager, UserOrderRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * Cette route permet d'afficher la page des commandes d'un utilisateur
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'list', methods: ['GET'])]
	public function index(Request $request): Response
	{
		// Lister les commandes de l'utilisateur
		$orders = $this->getUser()->getOrders();

		return $this->render('pages/order/list.html.twig', [
			'orders' => $orders,
		]);
	}

	/**
	 * Cette route permet d'afficher une commande en particulier
	 * 
	 * @param int $id L'identifiant de la commande
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/{id}', name: 'show', methods: ['GET'])]
	public function show(int $id): Response
	{
		// Récupérer la commande
		$order = $this->getRepository()->find($id);

		// Si la commande n'existe pas, renvoyer une erreur
		if (is_null($order)) {
			$this->addFlash('error', 'La commande demandée n\'existe pas !');
			return $this->redirectToRoute('app_order_list');
		}

		// Vérifier que la commande appartient à l'utilisateur
		if ($order->getUser() !== $this->getUser()) {
			$this->addFlash('error', 'Vous n\'avez pas le droit de voir cette commande !');
			return $this->redirectToRoute('app_order_list');
		}

		// Récupérer les lignes de commande
		$orderProductQuantities = $order->getOrderProductQuantities();

		return $this->render('pages/order/show.html.twig', [
			'order' => $order,
			'orderProductQuantities' => $orderProductQuantities
		]);
	}


	/**
	 * Cette route permet de créer une commande à partir des informations du formulaire de création de commande
	 * 
	 * @param Request $request La requête HTTP
	 * @param int $cartId L'identifiant du panier
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/create/{cartId}', name: 'create', methods: ['GET', 'POST'])]
	public function create(Request $request, int $cartId): Response
	{
		// Récupérer l'URL de la page d'où l'utilisateur vient
		$lastPage = $request->headers->get('referer');

		// Si l'utilisateur n'est pas connecté, empêcher l'action
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté effectuer une commande !');
			// Rediriger l'utilisateur à la page d'où il vient sinon à la page de connexion
			return $lastPage ? $this->redirect($lastPage) : $this->redirectToRoute('app_user_login');
		}

		// Récupérer le panier
		$cart = $this->getUser()->getCart();

		// Si le panier n'existe pas, le créer
		if (is_null($cart)) {
			try{
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la création du panier !');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Créer le formulaire de l'adresse de livraison
		$addressForm = $this->createForm(CustomerAddressType::class);

		// Traiter le formulaire
		$addressForm->handleRequest($request);

		if ($addressForm->isSubmitted() && $addressForm->isValid()) {
			// Récupérer les données du formulaire
			$address = $addressForm->getData();

			try {
				// Pour éviter de créer des commandes malgré les erreurs, on utilise une transaction
				$this->getEntityManager()->beginTransaction();

				// Enregistrer l'adresse
				$address->setUser($this->getUser());
				$this->getEntityManager()->getRepository(CustomerAddress::class)->save($address, true);

				$cartItems = $this->getEntityManager()->getRepository(CartProductQuantity::class)->findByAttributes(['cart' => $cart]);
				$totalPrice = 0.00;
				foreach ($cartItems as $cartItem) {
					$totalPrice = $totalPrice + ($cartItem->getProduct()->getPrice() * $cartItem->getQuantity());
				}
				$deliveryCost = $this->getEntityManager()->getRepository(Cart::class)->getDeliveryCost($totalPrice);

				// Créer la commande
				$orderCount = $this->getRepository()->count(['user' => $this->getUser()]);
				$order = new UserOrder([
					'number' => intval($cart->getUser()->getId().$cart->getUser()->getCart()->getId().$orderCount),
					'user' => $cart->getUser(),
					'isValidated' => false,
					'orderDate' => new \DateTime(),
					'customerAddress' => $address,
					'deliveryFee' => $deliveryCost,
				]);

				// Créer les lignes de commande
				$orderItems = [];
				foreach ($cartItems as $cartItem) {
					$orderItems[] = new OrderProductQuantity([
						'product' => $cartItem->getProduct(),
						'quantity' => $cartItem->getQuantity(),
						'originalOrder' => $order,
					]);
				}

				// Vider le panier
				foreach ($cartItems as $cartItem) {
					$this->getEntityManager()->getRepository(CartProductQuantity::class)->delete($cartItem, true);
				}

				// Enregistrer la commande
				$this->getRepository()->save($order, true);
				// Enregistrer les lignes de commande
				foreach ($orderItems as $orderItem) {
					$this->getEntityManager()->getRepository(OrderProductQuantity::class)->save($orderItem, true);
				}

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Votre commande (N°'.$order->getNumber().') a été créée avec succès !');
				return $this->redirectToRoute('app_order_show', ['id' => $order->getId()]);
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la création de la commande !');
			}
		}

		return $this->render('pages/order/create.html.twig', [
			'cart' => $cart,
			'addressForm' => $addressForm->createView(),
		]);
	}
}