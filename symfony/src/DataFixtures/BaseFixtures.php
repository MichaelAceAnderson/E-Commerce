<?php

namespace App\DataFixtures;

use App\Entity\AddressType;
use App\Entity\Cart;
use App\Entity\CartProductQuantity;
use App\Entity\Category;
use App\Entity\CustomerAddress;
use App\Entity\Media;
use App\Entity\OrderProductQuantity;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\UserOrder;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BaseFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
		// Initialiser les tableaux de propriétés
		// Créer les entités à partir des tableaux de propriétés
		// Mettre les entités dans des tableaux pour les réutiliser comme associations (ex: un produit a une catégorie, un utilisateur a une adresse, etc.)

        $addressTypes = [
            ['id' => 1, 'type' => 'Entreprise'],
            ['id' => 2, 'type' => 'Particulier'],
            ['id' => 3, 'type' => 'Autre'],
        ];
		$addressTypeObjects = [];
        foreach ($addressTypes as $addressType) {
            $entity = new AddressType();
            $entity->setType($addressType['type']);
			$addressTypeObjects[$addressType['id']] = $entity;
        }

        $users = [
            [
                'id' => 1,
                'last_name' => 'Gates',
                'first_name' => 'Bill',
                'phone' => 1234567890,
                'email' => 'bgates@microsoft.com',
                'roles' => ['ROLE_ADMIN'],
                'password' => $this->passwordHasher->hashPassword(new User(), 'bgates'),
                'is_verified' => true,
            ],
            [
                'id' => 2,
                'last_name' => 'Jobs',
                'first_name' => 'Steve',
                'phone' => 1234567891,
                'email' => 'sjobs@apple.com',
                'roles' => [],
                'password' => $this->passwordHasher->hashPassword(new User(), 'stjobs'),
                'is_verified' => true,
            ],
        ];
		$userObjects = [];
        foreach ($users as $user) {
            $entity = new User();
            $entity->setLastName($user['last_name']);
            $entity->setFirstName($user['first_name']);
            $entity->setPhone($user['phone']);
            $entity->setEmail($user['email']);
            $entity->setRoles($user['roles']);
            $entity->setPassword($user['password']);
            $entity->setIsVerified($user['is_verified']);
			$userObjects[$user['id']] = $entity;
        }

		$customerAddresses = [
			[
				'id' => 1,
				'type_id' => 3,
				'user_id' => 1,
				'last_name' => 'Gates',
				'first_name' => 'Bill',
				'phone' => 1234567890,
				'address' => '1, Microsoft Way',
				'postal_code' => 98052,
				'city' => 'Redmond',
				'country' => 'United States',
			],
			[
				'id' => 2,
				'type_id' => 3,
				'user_id' => 2,
				'last_name' => 'Jobs',
				'first_name' => 'Steve',
				'phone' => 1234567890,
				'address' => '1, Infinite Loop',
				'postal_code' => 95014,
				'city' => 'Cupertino',
				'country' => 'United States',
			],
		];
		$customerAddressObjects = [];
		foreach ($customerAddresses as $customerAddress) {
			$entity = new CustomerAddress();
			$entity->setType($addressTypeObjects[$customerAddress['type_id']]);
			$entity->setUser($userObjects[$customerAddress['user_id']]);
			$entity->setLastName($customerAddress['last_name']);
			$entity->setFirstName($customerAddress['first_name']);
			$entity->setPhone($customerAddress['phone']);
			$entity->setAddress($customerAddress['address']);
			$entity->setPostalCode($customerAddress['postal_code']);
			$entity->setCity($customerAddress['city']);
			$entity->setCountry($customerAddress['country']);
			$customerAddressObjects[$customerAddress['id']] = $entity;
		}

		// NOTE: Les chemins des médias par défaut suivent la nomenclature "genre-categorie-nom_produit-variante.extension"
		// Ex: unisex-accessories-sunglasses-wayfarer-black.png
		$medias = [
			[
				'id' => 1,
				'product_id' => 1,
				'path' => '/uploads/media/unisex-accessories-sunglasses-wayfarer-black.png',
				'alt' => 'Lunettes de soleil Wayfarer Noir',
				'type' => 'image',
			],
			[
				'id' => 2,
				'product_id' => 2,
				'path' => '/uploads/media/unisex-accessories-sunglasses-wayfarer-havana.png',
				'alt' => 'Lunettes de soleil Wayfarer Havanne',
				'type' => 'image',
			],
			[
				'id' => 3,
				'product_id' => 3,
				'path' => '/uploads/media/man-jackets-teddy.png',
				'alt' => 'Veste Teddy Homme',
				'type' => 'image',
			],
			[
				'id' => 4,
				'product_id' => 4,
				'path' => '/uploads/media/man-t_shirts-round_neck.png',
				'alt' => 'T-shirt à col rond Homme',
				'type' => 'image',
			],
			[
				'id' => 5,
				'product_id' => 5,
				'path' => '/uploads/media/man-t_shirts-v_neck.png',
				'alt' => 'T-shirt à col V Homme',
				'type' => 'image',
			],
			[
				'id' => 6,
				'product_id' => 6,
				'path' => '/uploads/media/unisex-t_shirts-logo-sunglasses.png',
				'alt' => 'T-shirt à motif lunettes de soleil Unisexe',
				'type' => 'image',
			],
			[
				'id' => 7,
				'product_id' => 7,
				'path' => '/uploads/media/man-t_shirts-henley.png',
				'alt' => 'T-shirt Henley Homme',
				'type' => 'image',
			],
			[
				'id' => 8,
				'product_id' => 8,
				'path' => '/uploads/media/man-jeans-straight.png',
				'alt' => 'Jean droit Homme',
				'type' => 'image',
			],
			[
				'id' => 9,
				'product_id' => 9,
				'path' => '/uploads/media/woman-t_shirts-round_neck.png',
				'alt' => 'T-shirt à col rond Femme',
				'type' => 'image',
			],
			[
				'id' => 10,
				'product_id' => 10,
				'path' => '/uploads/media/woman-t_shirts-v_neck.png',
				'alt' => 'T-shirt col V Femme',
				'type' => 'image',
			],
		];
		$mediaObjects = [];
		foreach ($medias as $media) {
			$entity = new Media();
			$entity->setProduct(null);
			$entity->setPath($media['path']);
			$entity->setAlt($media['alt']);
			$entity->setType($media['type']);
			$mediaObjects[$media['id']] = $entity;
		}

		$categories = [
			[
				'id' => 1,
				'media_id' => null,
				'name' => 'Accessoires',
				'description' => 'Montres, lunettes de soleil, ceintures, cravates et noeuds papillon',
			],
			[
				'id' => 2,
				'media_id' => null,
				'name' => 'Vestes de costume',
				'description' => 'Blazers, sportscoat et pièces de costume',
			],
			[
				'id' => 3,
				'media_id' => null,
				'name' => 'Hauts de survêtement',
				'description' => 'Vestes de survêtement/jogging',
			],
			[
				'id' => 4,
				'media_id' => null,
				'name' => 'Robes',
				'description' => 'Robes de soirée, robes de cocktail, robes de mariée',
			],
			[
				'id' => 5,
				'media_id' => null,
				'name' => 'Blousons',
				'description' => 'Vestes, manteaux, parkas',
			],
			[
				'id' => 6,
				'media_id' => null,
				'name' => 'Chemises',
				'description' => 'Chemises habillées, décontractées, en jean,...',
			],
			[
				'id' => 7,
				'media_id' => null,
				'name' => 'Pulls',
				'description' => 'Pulls, cardigans, sweaters',
			],
			[
				'id' => 8,
				'media_id' => null,
				'name' => 'Polos',
				'description' => 'Polos',
			],
			[
				'id' => 9,
				'media_id' => null,
				'name' => 'T-shirts',
				'description' => 'T-shirts, henleys, cols ronds, cols en V,...',
			],
			[
				'id' => 10,
				'media_id' => null,
				'name' => 'Pantalons de costume',
				'description' => 'Pantalons de costume, pantalons habillés',
			],
			[
				'id' => 11,
				'media_id' => null,
				'name' => 'Jupes',
				'description' => 'Jupes',
			],
			[
				'id' => 12,
				'media_id' => null,
				'name' => 'Jeans',
				'description' => 'Jeans, pantalons en denim',
			],
			[
				'id' => 13,
				'media_id' => null,
				'name' => 'Pantalons de survêtement',
				'description' => 'Pantalons de survêtement/jogging',
			],
			[
				'id' => 14,
				'media_id' => null,
				'name' => 'Chaussures habillées',
				'description' => 'Chaussures de ville, chaussures de cérémonie',
			],
			[
				'id' => 15,
				'media_id' => null,
				'name' => 'Chaussures à talon',
				'description' => 'Escarpins, sandales à talon',
			],
			[
				'id' => 16,
				'media_id' => null,
				'name' => 'Baskets',
				'description' => 'Baskets, sneakers',
			],
			[
				'id' => 17,
				'media_id' => null,
				'name' => 'Chaussures de sport',
				'description' => 'Chaussures de running, chaussures de fitness',
			],
		];
		$categoryObjects = [];
		foreach ($categories as $category) {
			$entity = new Category();
			$mediaId = $category['media_id'];
			if ($mediaId !== null) $entity->setMedia($mediaObjects[$mediaId]);
			$entity->setName($category['name']);
			$entity->setDescription($category['description']);
			$categoryObjects[$category['id']] = $entity;
		}

		$products = [
			[
				'id' => 1,
				'category_id' => 1,
				'name' => 'Lunettes de soleil Wayfarer noires',
				'description' => 'Lunettes de soleil Wayfarer noires',
				'price' => 150,
				'available' => true,
			],
			[
				'id' => 2,
				'category_id' => 1,
				'name' => 'Lunettes de soleil Wayfarer havanne',
				'description' => 'Lunettes de soleil Wayfarer havanne',
				'price' => 150,
				'available' => true,
			],
			[
				'id' => 3,
				'category_id' => 5,
				'name' => 'Veste Teddy Homme',
				'description' => 'Veste Teddy Homme',
				'price' => 50,
				'available' => true,
			],
			[
				'id' => 4,
				'category_id' => 9,
				'name' => 'T-shirt à col rond Homme',
				'description' => 'T-shirt à col rond Homme',
				'price' => 5,
				'available' => true,
			],
			[
				'id' => 5,
				'category_id' => 9,
				'name' => 'T-shirt à col V Homme',
				'description' => 'T-shirt à col V Homme',
				'price' => 5,
				'available' => true,
			],
			[
				'id' => 6,
				'category_id' => 1,
				'name' => 'T-shirt à motif lunettes de soleil',
				'description' => 'T-shirt à motif lunettes de soleil',
				'price' => 6,
				'available' => true,
			],
			[
				'id' => 7,
				'category_id' => 9,
				'name' => 'T-shirt Henley Homme',
				'description' => 'T-shirt Henley Homme',
				'price' => 6,
				'available' => true,
			],
			[
				'id' => 8,
				'category_id' => 12,
				'name' => 'Jean droit Homme',
				'description' => 'Jean droit Homme',
				'price' => 20,
				'available' => true,
			],
			[
				'id' => 9,
				'category_id' => 9,
				'name' => 'T-shirt à col rond Femme',
				'description' => 'T-shirt à col rond Femme',
				'price' => 5,
				'available' => true,
			],
			[
				'id' => 10,
				'category_id' => 9,
				'name' => 'T-shirt à col V Femme',
				'description' => 'T-shirt à col V Femme',
				'price' => 5,
				'available' => true,
			],
		];
		$productObjects = [];
		foreach ($products as $product) {
			$entity = new Product();
			$entity->setCategory($categoryObjects[$product['category_id']]);
			$entity->setName($product['name']);
			$entity->setDescription($product['description']);
			$entity->setPrice($product['price']);
			$entity->setAvailable($product['available']);
			$productObjects[$product['id']] = $entity;
		}

		$carts = [
			['id' => 1,'user_id' => 1],
		];
		$cartObjects = [];
		foreach ($carts as $cart) {
			$entity = new Cart();
			$entity->setUser($userObjects[$cart['user_id']]);
			$cartObjects[$cart['id']] = $entity;
		}

		$cartProductQuantities = [
			[
				'id' => 1,
				'product_id' => 1,
				'cart_id' => 1,
				'quantity' => 1,
			],
			[
				'id' => 2,
				'product_id' => 2,
				'cart_id' => 1,
				'quantity' => 3,
			],
			[
				'id' => 3,
				'product_id' => 3,
				'cart_id' => 1,
				'quantity' => 2,
			],
		];
		$cartProductQuantityObjects = [];
		foreach ($cartProductQuantities as $cartProductQuantity) {
			$entity = new CartProductQuantity();
			$entity->setProduct($productObjects[$cartProductQuantity['product_id']]);
			$entity->setCart($cartObjects[$cartProductQuantity['cart_id']]);
			$entity->setQuantity($cartProductQuantity['quantity']);
			$cartProductQuantityObjects[$cartProductQuantity['id']] = $entity;
		}

		$userOrders = [
			[
				'id' => 1,
				'user_id' => 1,
				'customer_address_id' => 1,
				'number' => 1,
				'is_validated' => true,
				'order_date' => '2024-02-03 23:43:06',
				'delivery_fee' => '5.00',
			],
		];
		$userOrderObjects = [];
		foreach ($userOrders as $userOrder) {
			$entity = new UserOrder();
			$entity->setUser($userObjects[$userOrder['user_id']]);
			$entity->setCustomerAddress($customerAddressObjects[$userOrder['customer_address_id']]);
			$entity->setNumber($userOrder['number']);
			$entity->setIsValidated($userOrder['is_validated']);
			$entity->setOrderDate(new \DateTime($userOrder['order_date']));
			$entity->setDeliveryFee($userOrder['delivery_fee']);
			$userOrderObjects[$userOrder['id']] = $entity;
		}

		$orderProductQuantities = [
			[
				'id' => 1,
				'product_id' => 1,
				'original_order_id' => 1,
				'quantity' => 1,
			],
			[
				'id' => 2,
				'product_id' => 2,
				'original_order_id' => 1,
				'quantity' => 3,
			],
			[
				'id' => 3,
				'product_id' => 3,
				'original_order_id' => 1,
				'quantity' => 2,
			],
		];
		$orderProductQuantityObjects = [];
		foreach ($orderProductQuantities as $orderProductQuantity) {
			$entity = new OrderProductQuantity();
			$entity->setProduct($productObjects[$orderProductQuantity['product_id']]);
			$entity->setOriginalOrder($userOrderObjects[$orderProductQuantity['original_order_id']]);
			$entity->setQuantity($orderProductQuantity['quantity']);
			$orderProductQuantityObjects[$orderProductQuantity['id']] = $entity;
		}

		// Refaire une passe sur les médias pour ajouter les produits
		foreach ($medias as $media) {
			$productId = $media['product_id'];
			if ($productId !== null) {
				$mediaObjects[$media['id']]->setProduct($productObjects[$productId]);
			}
		}


		// Sauvegarder les entités
		foreach ($addressTypeObjects as $addressType) {
			$manager->persist($addressType);
		}
		foreach ($userObjects as $user) {
			$manager->persist($user);
		}
		foreach ($customerAddressObjects as $customerAddress) {
			$manager->persist($customerAddress);
		}
		foreach ($mediaObjects as $media) {
			$manager->persist($media);
		}
		foreach ($categoryObjects as $category) {
			$manager->persist($category);
		}
		foreach ($productObjects as $product) {
			$manager->persist($product);
		}
		foreach ($cartObjects as $cart) {
			$manager->persist($cart);
		}
		foreach ($cartProductQuantityObjects as $cartProductQuantity) {
			$manager->persist($cartProductQuantity);
		}
		foreach ($userOrderObjects as $userOrder) {
			$manager->persist($userOrder);
		}
		foreach ($orderProductQuantityObjects as $orderProductQuantity) {
			$manager->persist($orderProductQuantity);
		}		

        $manager->flush();
    }
}