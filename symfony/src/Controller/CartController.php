<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartProductQuantity;
use App\Entity\Product;
use App\Form\CartProductQuantityType;
use App\Repository\CartRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/cart', name: 'app_cart_')]
class CartController extends AbstractEntityController
{
	/**
	 * Créer le contrôleur de l'entité
	 * 
	 * @param EntityManagerInterface $entityManager Le service de gestion des entités
	 * @param CartRepository $entityRepository Le repository de l'entité
	 * 
	 * @return static Un contrôleur pour l'entité
	 */
	public function __construct(EntityManagerInterface $entityManager, CartRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * Cette route permet d'afficher la page du panier
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/', name: 'show', methods: ['GET', 'POST'])]
	public function index(Request $request): Response
	{
		// Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour accéder à votre panier');
			return $this->redirectToRoute('app_user_login');
		}

		// Récupérer le panier
		$cart = $this->getUser()->getCart();

		// Si le panier n'existe pas, le créer
		if (is_null($cart)) {
			try {
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la création de votre panier');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Récupérer les produits du panier
		$cartProductQuantities = $cart->getCartProductQuantities();

		$totalPrice = 0;

		$formViews = [];
		foreach ($cartProductQuantities as $cartProductQuantity) {
			$totalPrice += $cartProductQuantity->getProduct()->getPrice() * $cartProductQuantity->getQuantity();

			// Afin de distinguer les formulaires, il est nécessaire de leur attribuer une action unique à chacun
			$form = $this->createForm(CartProductQuantityType::class, $cartProductQuantity, [
				'action' => $this->generateUrl('app_cart_update', ['id' => $cartProductQuantity->getId()]),
			]);
			$form->handleRequest($request);
			$formViews[] = $form->createView();
		}

		return $this->render('pages/cart/show.html.twig', [
			'cart' => $cart,
			'cartProductQuantities' => $cartProductQuantities,
			'formViews' => $formViews,
			'estimatedDeliveryCost' => $this->getRepository()->getDeliveryCost($totalPrice),
		]);
	}

	/**
	 * Cette route permet de modifier la quantité d'un produit dans le panier
	 * 
	 * @param Request $request La requête HTTP
	 * @param int $id L'identifiant de l'objet CartProductQuantity
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/update/{id}', name: 'update', methods: ['POST'])]
	public function update(Request $request, int $id): Response
	{
		// Si l'utilisateur n'est pas connecté, empêcher l'action
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour modifier la quantité d\'un produit dans votre panier !');
			// Rediriger l'utilisateur à la page d'où il vient sinon à la page de connexion
			$url = $request->headers->get('referer');
			return $url ? $this->redirect($url) : $this->redirectToRoute('app_user_login');
		}

		// Récupérer le panier
		$cart = $this->getUser()->getCart();

		// Si le panier n'existe pas, le créer
		if (is_null($cart)) {
			try {
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la création de votre panier');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Récupérer la quantité du produit dans le panier
		$cartProductQuantity = $this->getEntityManager()->getRepository(CartProductQuantity::class)->find($id);

		// Si le produit n'est pas dans le panier, afficher une erreur
		if (is_null($cartProductQuantity) || $cartProductQuantity->getCart() !== $cart) {
			$this->addFlash('error', 'Le produit n\'est pas dans le panier');
			return $this->redirectToRoute('app_cart_show');
		}

		// Le formulaire doit être recréé ici pour que son action puisse être traitée
		$form = $this->createForm(CartProductQuantityType::class, $cartProductQuantity);
		$form->handleRequest($request);

		// Si le formulaire a été soumis et est valide
		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				try {
					$this->getEntityManager()->beginTransaction();

					// Récupérer la nouvelle quantité
					$quantity = $form->get('quantity')->getData();
					$cartProductQuantity->setQuantity($quantity);
					// Enregistrer la nouvelle quantité
					$this->getEntityManager()->getRepository(CartProductQuantity::class)->save($cartProductQuantity, true);

					$this->getEntityManager()->commit();
					$this->addFlash('success', 'La quantité a été modifiée');
				} catch (\Exception $e) {
					$this->getEntityManager()->rollback();
					$this->addFlash('error', 'Une erreur est survenue lors de la modification de la quantité');
					return $this->redirectToRoute('app_cart_show');
				}
			} else {
				// En raison de la redirection de l'action, les erreurs ne peuvent pas être affichées directement
				// Elles doivent donc être affichées manuellement
				$errorMsg = '';
				foreach ($form->getErrors(true) as $error) {
					$errorMsg .= $error->getMessage() . PHP_EOL;
				}
				$this->addFlash('error', $errorMsg);
			}
		}

		return $this->redirectToRoute('app_cart_show');
	}

	/**
	 * Cette route permet d'ajouter un produit au panier
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/add/{id}', name: 'add_product', methods: ['POST'])]
	public function add(Request $request, int $id): Response
	{
		// Récupérer l'URL de la page d'où l'utilisateur vient
		$lastPage = $request->headers->get('referer');

		// Récupérer la quantité du produit à ajouter
		$quantity = $request->request->get('quantity') ?? 1;

		// Si l'utilisateur n'est pas connecté, empêcher l'action
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour ajouter un produit à votre panier !');
			// Rediriger l'utilisateur à la page d'où il vient sinon à la page de connexion
			return $lastPage ? $this->redirect($lastPage) : $this->redirectToRoute('app_user_login');
		}

		// Récupérer le panier
		$cart = $this->getUser()->getCart();

		// Si le panier n'existe pas, le créer
		if (is_null($cart)) {
			try {
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la création de votre panier');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Récupérer le produit
		$product = $this->getEntityManager()->getRepository(Product::class)->find($id);

		// Si le produit n'existe pas, afficher une erreur
		if (!$product) {
			$this->addFlash('error', 'Le produit n\'existe pas');
			return $this->redirectToRoute('app_product_list');
		}

		// Récupérer la quantité du produit dans le panier
		$productQuantities = $this->getEntityManager()->getRepository(CartProductQuantity::class)->findByAttributes(['cart' => $cart, 'product' => $product]);

		$productQuantity = null;
		try {
			$this->getEntityManager()->beginTransaction();

			// Si le produit n'est pas dans le panier, l'ajouter
			if (empty($productQuantities)) {
				$productQuantity = new CartProductQuantity(['cart' => $cart, 'product' => $product, 'quantity' => $quantity]);
				$this->getEntityManager()->getRepository(CartProductQuantity::class)->save($productQuantity, true);
			} else {
				$productQuantity = $productQuantities[0];
				$productQuantity->setQuantity($productQuantity->getQuantity() + $quantity);
				$this->getEntityManager()->getRepository(CartProductQuantity::class)->save($productQuantity, true);
			}

			$this->getEntityManager()->commit();
			$this->addFlash('success', ['msg_1' => 'Vous avez ajouté ', 'b' => $quantity, 'msg_2' => ' x ', 'b_2' => $product->getName(), 'msg_4' => ' à votre panier']);
		} catch (\Exception $e) {
			$this->getEntityManager()->rollback();
			$this->addFlash('error', 'Une erreur est survenue lors de l\'ajout du produit à votre panier');
		}

		return $lastPage ? $this->redirect($lastPage) : $this->redirectToRoute('app_product_list');
	}

	/**
	 * Cette route permet de retirer un produit du panier
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/remove/{id}', name: 'remove_product', methods: ['POST'])]
	public function remove(Request $request, int $id): Response
	{
		// Si l'utilisateur n'est pas connecté, empêcher l'action
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour retirer un produit de votre panier !');
			// Rediriger l'utilisateur à la page d'où il vient sinon à la page de connexion
			$url = $request->headers->get('referer');
			return $url ? $this->redirect($url) : $this->redirectToRoute('app_user_login');
		}

		// Récupérer le panier
		$cart = $this->getUser()->getCart();

		// Si le panier n'existe pas, le créer
		if (is_null($cart)) {
			try {
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la création de votre panier');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Récupérer le produit
		$product = $this->getEntityManager()->getRepository(Product::class)->find($id);

		// Si le produit n'existe pas, afficher une erreur
		if (is_null($product)) {
			$this->addFlash('error', 'Le produit n\'existe pas');
			return $this->redirectToRoute('app_cart_show');
		}

		// Récupérer la quantité du produit dans le panier
		$cartProductQuantity = $this->getEntityManager()->getRepository(CartProductQuantity::class)->findByAttributes(['cart' => $cart, 'product' => $product]);

		// Si le produit n'est pas dans le panier, afficher une erreur
		if (empty($cartProductQuantity)) {
			$this->addFlash('error', 'Le produit n\'est pas dans le panier');
			return $this->redirectToRoute('app_cart_show');
		} else {
			$cartProductQuantity = $cartProductQuantity[0];
		}

		try{
			$this->getEntityManager()->beginTransaction();

			// Récupérer la quantité du produit à retirer
			$quantity = $cartProductQuantity->getQuantity();

			// Retirer ce type de produit du panier
			$this->getEntityManager()->getRepository(CartProductQuantity::class)->delete($cartProductQuantity, true);

			$this->getEntityManager()->commit();
			$this->addFlash('success', ['msg_1' => 'Vous avez retiré ', 'b' => $quantity, 'msg_2' => ' x ', 'b_2' => $product->getName(), 'msg_4' => ' de votre panier']);
		}
		catch (\Exception $e) {
			$this->getEntityManager()->rollback();
			$this->addFlash('error', 'Une erreur est survenue lors du retrait du produit de votre panier');
		}

		return $this->redirectToRoute('app_cart_show');
	}

	/**
	 * Cette route permet de vider le panier
	 * 
	 * @param Request $request La requête HTTP
	 * 
	 * @return Response La réponse HTTP
	 */
	#[Route('/empty', name: 'empty', methods: ['POST'])]
	public function empty(Request $request): Response
	{
		// Rediriger l'utilisateur à la page d'où il vient sinon à la page de connexion
		$lastPage = $request->headers->get('referer');

		// Si l'utilisateur n'est pas connecté, empêcher l'action
		if (!$this->getUser()) {
			$this->addFlash('error', 'Vous devez être connecté pour vider votre panier !');
			return $lastPage ? $this->redirect($lastPage) : $this->redirectToRoute('app_user_login');
		}

		// Récupérer le panier
		$cart = $this->getUser()->getCart();

		// Si le panier n'existe pas, le créer
		if (is_null($cart)) {
			try {
				$this->getEntityManager()->beginTransaction();

				$cart = new Cart(['user' => $this->getUser()]);
				$this->getUser()->setCart($cart);
				$this->getRepository()->save($cart, true);

				$this->getEntityManager()->commit();
			} catch (\Exception $e) {
				$this->addFlash('error', 'Une erreur est survenue lors de la création de votre panier');
				return $this->redirectToRoute('app_cart_show');
			}
		}

		// Récupérer les produits du panier
		$cartProductQuantities = $cart->getCartProductQuantities();

		// Si le panier n'a pas de produits, afficher une erreur
		if ($cartProductQuantities->isEmpty()) {
			$this->addFlash('error', 'Le panier est déjà vide');
		}
		else{
			// Supprimer les produits du panier
			try {
				$this->getEntityManager()->beginTransaction();

				foreach ($cartProductQuantities as $cartProductQuantity) {
					$this->getEntityManager()->getRepository(CartProductQuantity::class)->delete($cartProductQuantity, true);
				}

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'Le panier a été vidé');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'Une erreur est survenue lors de la suppression des produits du panier');
			}
		}
		return $this->redirectToRoute('app_cart_show');
	}

}