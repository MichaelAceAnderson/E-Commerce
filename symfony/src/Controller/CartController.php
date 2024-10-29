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
	 * Create the entity controller
	 * 
	 * @param EntityManagerInterface $entityManager The entity manager service
	 * @param CartRepository $entityRepository The entity repository
	 * 
	 * @return static An entity controller
	 */
	public function __construct(EntityManagerInterface $entityManager, CartRepository $repository)
	{
		parent::__construct($entityManager, $repository);
	}

	/**
	 * This route displays the cart page
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/', name: 'show', methods: ['GET', 'POST'])]
	public function index(Request $request): Response
	{
		// If the user is not logged in, redirect to the login page
		if (!$this->getUser()) {
			$this->addFlash('error', 'You must be logged in to access your cart');
			return $this->redirectToRoute('app_user_login');
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
				$this->addFlash('error', 'An error occurred while creating your cart');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Retrieve the products in the cart
		$cartProductQuantities = $cart->getCartProductQuantities();

		$totalPrice = 0;

		$formViews = [];
		foreach ($cartProductQuantities as $cartProductQuantity) {
			$totalPrice += $cartProductQuantity->getProduct()->getPrice() * $cartProductQuantity->getQuantity();

			// To distinguish the forms, it is necessary to assign a unique action to each
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
	 * This route allows modifying the quantity of a product in the cart
	 * 
	 * @param Request $request The HTTP request
	 * @param int $id The identifier of the CartProductQuantity object
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/update/{id}', name: 'update', methods: ['POST'])]
	public function update(Request $request, int $id): Response
	{
		// If the user is not logged in, prevent the action
		if (!$this->getUser()) {
			$this->addFlash('error', 'You must be logged in to modify the quantity of a product in your cart!');
			// Redirect the user to the previous page or to the login page
			$url = $request->headers->get('referer');
			return $url ? $this->redirect($url) : $this->redirectToRoute('app_user_login');
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
				$this->addFlash('error', 'An error occurred while creating your cart');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Retrieve the product quantity in the cart
		$cartProductQuantity = $this->getEntityManager()->getRepository(CartProductQuantity::class)->find($id);

		// If the product is not in the cart, display an error
		if (is_null($cartProductQuantity) || $cartProductQuantity->getCart() !== $cart) {
			$this->addFlash('error', 'The product is not in the cart');
			return $this->redirectToRoute('app_cart_show');
		}

		// The form must be recreated here so that its action can be processed
		$form = $this->createForm(CartProductQuantityType::class, $cartProductQuantity);
		$form->handleRequest($request);

		// If the form has been submitted and is valid
		if ($form->isSubmitted()) {
			if ($form->isValid()) {
				try {
					$this->getEntityManager()->beginTransaction();

					// Retrieve the new quantity
					$quantity = $form->get('quantity')->getData();
					$cartProductQuantity->setQuantity($quantity);
					// Save the new quantity
					$this->getEntityManager()->getRepository(CartProductQuantity::class)->save($cartProductQuantity, true);

					$this->getEntityManager()->commit();
					$this->addFlash('success', 'The quantity has been modified');
				} catch (\Exception $e) {
					$this->getEntityManager()->rollback();
					$this->addFlash('error', 'An error occurred while modifying the quantity');
					return $this->redirectToRoute('app_cart_show');
				}
			} else {
				// Due to the action redirection, errors cannot be displayed directly
				// They must be displayed manually
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
	 * This route allows adding a product to the cart
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/add/{id}', name: 'add_product', methods: ['POST'])]
	public function add(Request $request, int $id): Response
	{
		// Retrieve the URL of the previous page
		$lastPage = $request->headers->get('referer');

		// Retrieve the quantity of the product to add
		$quantity = $request->request->get('quantity') ?? 1;

		// If the user is not logged in, prevent the action
		if (!$this->getUser()) {
			$this->addFlash('error', 'You must be logged in to add a product to your cart!');
			// Redirect the user to the previous page or to the login page
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
				$this->addFlash('error', 'An error occurred while creating your cart');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Retrieve the product
		$product = $this->getEntityManager()->getRepository(Product::class)->find($id);

		// If the product does not exist, display an error
		if (!$product) {
			$this->addFlash('error', 'The product does not exist');
			return $this->redirectToRoute('app_product_list');
		}

		// Retrieve the product quantity in the cart
		$productQuantities = $this->getEntityManager()->getRepository(CartProductQuantity::class)->findByAttributes(['cart' => $cart, 'product' => $product]);

		$productQuantity = null;
		try {
			$this->getEntityManager()->beginTransaction();

			// If the product is not in the cart, add it
			if (empty($productQuantities)) {
				$productQuantity = new CartProductQuantity(['cart' => $cart, 'product' => $product, 'quantity' => $quantity]);
				$this->getEntityManager()->getRepository(CartProductQuantity::class)->save($productQuantity, true);
			} else {
				$productQuantity = $productQuantities[0];
				$productQuantity->setQuantity($productQuantity->getQuantity() + $quantity);
				$this->getEntityManager()->getRepository(CartProductQuantity::class)->save($productQuantity, true);
			}

			$this->getEntityManager()->commit();
			$this->addFlash('success', ['msg_1' => 'You have added ', 'b' => $quantity, 'msg_2' => ' x ', 'b_2' => $product->getName(), 'msg_4' => ' to your cart']);
		} catch (\Exception $e) {
			$this->getEntityManager()->rollback();
			$this->addFlash('error', 'An error occurred while adding the product to your cart');
		}

		return $lastPage ? $this->redirect($lastPage) : $this->redirectToRoute('app_product_list');
	}

	/**
	 * This route allows removing a product from the cart
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/remove/{id}', name: 'remove_product', methods: ['POST'])]
	public function remove(Request $request, int $id): Response
	{
		// If the user is not logged in, prevent the action
		if (!$this->getUser()) {
			$this->addFlash('error', 'You must be logged in to remove a product from your cart!');
			// Redirect the user to the previous page or to the login page
			$url = $request->headers->get('referer');
			return $url ? $this->redirect($url) : $this->redirectToRoute('app_user_login');
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
				$this->addFlash('error', 'An error occurred while creating your cart');
				return $this->redirectToRoute('app_product_list');
			}
		}

		// Retrieve the product
		$product = $this->getEntityManager()->getRepository(Product::class)->find($id);

		// If the product does not exist, display an error
		if (is_null($product)) {
			$this->addFlash('error', 'The product does not exist');
			return $this->redirectToRoute('app_cart_show');
		}

		// Retrieve the product quantity in the cart
		$cartProductQuantity = $this->getEntityManager()->getRepository(CartProductQuantity::class)->findByAttributes(['cart' => $cart, 'product' => $product]);

		// If the product is not in the cart, display an error
		if (empty($cartProductQuantity)) {
			$this->addFlash('error', 'The product is not in the cart');
			return $this->redirectToRoute('app_cart_show');
		} else {
			$cartProductQuantity = $cartProductQuantity[0];
		}

		try{
			$this->getEntityManager()->beginTransaction();

			// Retrieve the quantity of the product to remove
			$quantity = $cartProductQuantity->getQuantity();

			// Remove this type of product from the cart
			$this->getEntityManager()->getRepository(CartProductQuantity::class)->delete($cartProductQuantity, true);

			$this->getEntityManager()->commit();
			$this->addFlash('success', ['msg_1' => 'You have removed ', 'b' => $quantity, 'msg_2' => ' x ', 'b_2' => $product->getName(), 'msg_4' => ' from your cart']);
		}
		catch (\Exception $e) {
			$this->getEntityManager()->rollback();
			$this->addFlash('error', 'An error occurred while removing the product from your cart');
		}

		return $this->redirectToRoute('app_cart_show');
	}

	/**
	 * This route allows emptying the cart
	 * 
	 * @param Request $request The HTTP request
	 * 
	 * @return Response The HTTP response
	 */
	#[Route('/empty', name: 'empty', methods: ['POST'])]
	public function empty(Request $request): Response
	{
		// Redirect the user to the previous page or to the login page
		$lastPage = $request->headers->get('referer');

		// If the user is not logged in, prevent the action
		if (!$this->getUser()) {
			$this->addFlash('error', 'You must be logged in to empty your cart!');
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
				$this->addFlash('error', 'An error occurred while creating your cart');
				return $this->redirectToRoute('app_cart_show');
			}
		}

		// Retrieve the products in the cart
		$cartProductQuantities = $cart->getCartProductQuantities();

		// If the cart has no products, display an error
		if ($cartProductQuantities->isEmpty()) {
			$this->addFlash('error', 'The cart is already empty');
		}
		else{
			// Remove the products from the cart
			try {
				$this->getEntityManager()->beginTransaction();

				foreach ($cartProductQuantities as $cartProductQuantity) {
					$this->getEntityManager()->getRepository(CartProductQuantity::class)->delete($cartProductQuantity, true);
				}

				$this->getEntityManager()->commit();
				$this->addFlash('success', 'The cart has been emptied');
			} catch (\Exception $e) {
				$this->getEntityManager()->rollback();
				$this->addFlash('error', 'An error occurred while removing the products from the cart');
			}
		}
		return $this->redirectToRoute('app_cart_show');
	}

}