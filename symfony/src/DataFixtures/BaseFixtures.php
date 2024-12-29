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
		// Initialize property arrays
		// Create entities from property arrays
		// Put entities into arrays to reuse them as associations (e.g., a product has a category, a user has an address, etc.)

		$addressTypes = [
			['id' => 1, 'type' => 'Business'],
			['id' => 2, 'type' => 'Individual'],
			['id' => 3, 'type' => 'Other'],
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

		// NOTE: The default media paths follow the naming convention "gender-category-product_name-variant.extension"
		// Ex: unisex-accessories-sunglasses-wayfarer-black.png
		$medias = [
			[
				'id' => 1,
				'product_id' => 1,
				'path' => '/uploads/media/unisex-accessories-sunglasses-wayfarer-black.png',
				'alt' => 'Black Wayfarer Sunglasses',
				'type' => 'image',
			],
			[
				'id' => 2,
				'product_id' => 2,
				'path' => '/uploads/media/unisex-accessories-sunglasses-wayfarer-havana.png',
				'alt' => 'Havana Wayfarer Sunglasses',
				'type' => 'image',
			],
			[
				'id' => 3,
				'product_id' => 3,
				'path' => '/uploads/media/man-jackets-teddy.png',
				'alt' => 'Men\'s Teddy Jacket',
				'type' => 'image',
			],
			[
				'id' => 4,
				'product_id' => 4,
				'path' => '/uploads/media/man-t_shirts-round_neck.png',
				'alt' => 'Men\'s Round Neck T-shirt',
				'type' => 'image',
			],
			[
				'id' => 5,
				'product_id' => 5,
				'path' => '/uploads/media/man-t_shirts-v_neck.png',
				'alt' => 'Men\'s V-neck T-shirt',
				'type' => 'image',
			],
			[
				'id' => 6,
				'product_id' => 6,
				'path' => '/uploads/media/unisex-t_shirts-logo-sunglasses.png',
				'alt' => 'Unisex T-shirt with Sunglasses Logo',
				'type' => 'image',
			],
			[
				'id' => 7,
				'product_id' => 7,
				'path' => '/uploads/media/man-t_shirts-henley.png',
				'alt' => 'Men\'s Henley T-shirt',
				'type' => 'image',
			],
			[
				'id' => 8,
				'product_id' => 8,
				'path' => '/uploads/media/man-jeans-straight.png',
				'alt' => 'Men\'s Straight Jeans',
				'type' => 'image',
			],
			[
				'id' => 9,
				'product_id' => 9,
				'path' => '/uploads/media/woman-t_shirts-round_neck.png',
				'alt' => 'Women\'s Round Neck T-shirt',
				'type' => 'image',
			],
			[
				'id' => 10,
				'product_id' => 10,
				'path' => '/uploads/media/woman-t_shirts-v_neck.png',
				'alt' => 'Women\'s V-neck T-shirt',
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
				'name' => 'Accessories',
				'description' => 'Watches, sunglasses, belts, ties, and bow ties',
			],
			[
				'id' => 2,
				'media_id' => null,
				'name' => 'Suit Jackets',
				'description' => 'Blazers, sports coats, and suit pieces',
			],
			[
				'id' => 3,
				'media_id' => null,
				'name' => 'Tracksuit Tops',
				'description' => 'Tracksuit/jogging jackets',
			],
			[
				'id' => 4,
				'media_id' => null,
				'name' => 'Dresses',
				'description' => 'Evening dresses, cocktail dresses, wedding dresses',
			],
			[
				'id' => 5,
				'media_id' => null,
				'name' => 'Jackets',
				'description' => 'Jackets, coats, parkas',
			],
			[
				'id' => 6,
				'media_id' => null,
				'name' => 'Shirts',
				'description' => 'Dress shirts, casual shirts, denim shirts,...',
			],
			[
				'id' => 7,
				'media_id' => null,
				'name' => 'Sweaters',
				'description' => 'Sweaters, cardigans, pullovers',
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
				'description' => 'T-shirts, henleys, crew necks, V-necks,...',
			],
			[
				'id' => 10,
				'media_id' => null,
				'name' => 'Dress Pants',
				'description' => 'Dress pants, formal pants',
			],
			[
				'id' => 11,
				'media_id' => null,
				'name' => 'Skirts',
				'description' => 'Skirts',
			],
			[
				'id' => 12,
				'media_id' => null,
				'name' => 'Jeans',
				'description' => 'Jeans, denim pants',
			],
			[
				'id' => 13,
				'media_id' => null,
				'name' => 'Tracksuit Pants',
				'description' => 'Tracksuit/jogging pants',
			],
			[
				'id' => 14,
				'media_id' => null,
				'name' => 'Dress Shoes',
				'description' => 'Dress shoes, formal shoes',
			],
			[
				'id' => 15,
				'media_id' => null,
				'name' => 'Heeled Shoes',
				'description' => 'Pumps, heeled sandals',
			],
			[
				'id' => 16,
				'media_id' => null,
				'name' => 'Sneakers',
				'description' => 'Sneakers, trainers',
			],
			[
				'id' => 17,
				'media_id' => null,
				'name' => 'Sports Shoes',
				'description' => 'Running shoes, fitness shoes',
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
				'name' => 'Black Wayfarer Sunglasses',
				'description' => 'Black Wayfarer Sunglasses',
				'price' => 150,
				'available' => true,
			],
			[
				'id' => 2,
				'category_id' => 1,
				'name' => 'Havana Wayfarer Sunglasses',
				'description' => 'Havana Wayfarer Sunglasses',
				'price' => 150,
				'available' => true,
			],
			[
				'id' => 3,
				'category_id' => 5,
				'name' => 'Men\'s Teddy Jacket',
				'description' => 'Men\'s Teddy Jacket',
				'price' => 50,
				'available' => true,
			],
			[
				'id' => 4,
				'category_id' => 9,
				'name' => 'Men\'s Round Neck T-shirt',
				'description' => 'Men\'s Round Neck T-shirt',
				'price' => 5,
				'available' => true,
			],
			[
				'id' => 5,
				'category_id' => 9,
				'name' => 'Men\'s V-neck T-shirt',
				'description' => 'Men\'s V-neck T-shirt',
				'price' => 5,
				'available' => true,
			],
			[
				'id' => 6,
				'category_id' => 1,
				'name' => 'T-shirt with Sunglasses Logo',
				'description' => 'T-shirt with Sunglasses Logo',
				'price' => 6,
				'available' => true,
			],
			[
				'id' => 7,
				'category_id' => 9,
				'name' => 'Men\'s Henley T-shirt',
				'description' => 'Men\'s Henley T-shirt',
				'price' => 6,
				'available' => true,
			],
			[
				'id' => 8,
				'category_id' => 12,
				'name' => 'Men\'s Straight Jeans',
				'description' => 'Men\'s Straight Jeans',
				'price' => 20,
				'available' => true,
			],
			[
				'id' => 9,
				'category_id' => 9,
				'name' => 'Women\'s Round Neck T-shirt',
				'description' => 'Women\'s Round Neck T-shirt',
				'price' => 5,
				'available' => true,
			],
			[
				'id' => 10,
				'category_id' => 9,
				'name' => 'Women\'s V-neck T-shirt',
				'description' => 'Women\'s V-neck T-shirt',
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