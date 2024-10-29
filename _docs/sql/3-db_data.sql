-- Adminer 4.8.1 MySQL 11.3.2-MariaDB-1:11.3.2+maria~ubu2204-log dump
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `symfony_ecommerce`;

SET NAMES utf8mb4;

INSERT INTO `address_type` (`id`, `type`) VALUES
(1, 'Company'),
(2, 'Individual'),
(3, 'Other');

INSERT INTO `cart` (`id`, `user_id`) VALUES
(1, 1);

INSERT INTO `cart_product_quantity` (`id`, `product_id`, `cart_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 3),
(3, 3, 1, 2);

INSERT INTO `category` (`id`, `media_id`, `name`, `description`) VALUES
(1, NULL, 'Accessories', 'Watches, sunglasses, belts, ties, and bow ties'),
(2, NULL, 'Jackets', 'Jackets, coats, parkas'),
(3, NULL, 'T-shirts', 'T-shirts, henleys, crew necks, V-necks, ...'),
(4, NULL, 'Jeans', 'Jeans, denim pants'),
(5, NULL, 'Suit Jackets', 'Blazers, sports coats, and suit pieces'),
(6, NULL, 'Track Tops', 'Track jackets/jogging tops'),
(7, NULL, 'Dresses', 'Evening dresses, cocktail dresses, wedding dresses'),
(8, NULL, 'Shirts', 'Dress shirts, casual shirts, denim shirts, ...'),
(9, NULL, 'Sweaters', 'Sweaters, cardigans, pullovers'),
(10, NULL, 'Polos', 'Polos'),
(11, NULL, 'Suit Pants', 'Suit pants, dress pants'),
(12, NULL, 'Skirts', 'Skirts'),
(13, NULL, 'Track Pants', 'Track pants/jogging pants'),
(14, NULL, 'Dress Shoes', 'Dress shoes, formal shoes'),
(15, NULL, 'Heels', 'Heels, heeled sandals'),
(16, NULL, 'Sneakers', 'Sneakers, trainers'),
(17, NULL, 'Sports Shoes', 'Running shoes, fitness shoes');

INSERT INTO `customer_address` (`id`, `type_id`, `user_id`, `last_name`, `first_name`, `phone`, `address`, `postal_code`, `city`, `country`) VALUES
(1, 3, 1, 'Gates', 'Bill', '1234567890', '1, Microsoft Way', '98052', 'Redmond', 'United States'),
(2, 3, 2, 'Jobs', 'Steve', '1234567890', '1, Infinite Loop', '95014', 'Cupertino', 'United States');

INSERT INTO `media` (`id`, `product_id`, `path`, `alt`, `type`) VALUES
(1, 1, '/uploads/media/unisex-accessories-sunglasses-wayfarer-black.png', 'Black Wayfarer Sunglasses', 'image'),
(2, 2, '/uploads/media/unisex-accessories-sunglasses-wayfarer-havana.png', 'Havana Wayfarer Sunglasses', 'image'),
(3, 3, '/uploads/media/man-jackets-teddy.png', 'Men\'s Teddy Jacket', 'image'),
(4, 4, '/uploads/media/man-t_shirts-round_neck.png', 'Men\'s Crew Neck T-shirt', 'image'),
(5, 5, '/uploads/media/man-t_shirts-v_neck.png', 'Men\'s V-neck T-shirt', 'image'),
(6, 6, '/uploads/media/unisex-t_shirts-logo-sunglasses.png', 'Unisex Sunglasses Logo T-shirt', 'image'),
(7, 7, '/uploads/media/man-t_shirts-henley.png', 'Men\'s Henley T-shirt', 'image'),
(8, 8, '/uploads/media/man-jeans-straight.png', 'Men\'s Straight Jeans', 'image'),
(9, 9, '/uploads/media/woman-t_shirts-round_neck.png', 'Women\'s Crew Neck T-shirt', 'image'),
(10, 10, '/uploads/media/woman-t_shirts-v_neck.png', 'Women\'s V-neck T-shirt', 'image');

INSERT INTO `order_product_quantity` (`id`, `product_id`, `original_order_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 2, 1, 3),
(3, 3, 1, 2);

INSERT INTO `product` (`id`, `category_id`, `name`, `description`, `price`, `available`) VALUES
(1, 1, 'Black Wayfarer Sunglasses', 'Black Wayfarer Sunglasses', 150, 1),
(2, 1, 'Havana Wayfarer Sunglasses', 'Havana Wayfarer Sunglasses', 150, 1),
(3, 2, 'Men\'s Teddy Jacket', 'Men\'s Teddy Jacket', 50, 1),
(4, 3, 'Men\'s Crew Neck T-shirt', 'Men\'s Crew Neck T-shirt', 5, 1),
(5, 3, 'Men\'s V-neck T-shirt', 'Men\'s V-neck T-shirt', 5, 1),
(6, 1, 'Sunglasses Logo T-shirt', 'Sunglasses Logo T-shirt', 6, 1),
(7, 3, 'Men\'s Henley T-shirt', 'Men\'s Henley T-shirt', 6, 1),
(8, 4, 'Men\'s Straight Jeans', 'Men\'s Straight Jeans', 20, 1),
(9, 3, 'Women\'s Crew Neck T-shirt', 'Women\'s Crew Neck T-shirt', 5, 1),
(10, 3, 'Women\'s V-neck T-shirt', 'Women\'s V-neck T-shirt', 5, 1);

INSERT INTO `user` (`id`, `last_name`, `first_name`, `phone`, `email`, `password`, `is_verified`, `roles`) VALUES
(1, 'Gates', 'Bill', '1234567890', 'bgates@microsoft.com', '$2y$13$kDKX.7WfhndZwWSsgQYi2Oq7SAQoveMiY/ghxuNjT16L/VL4QcP3e', 1, '[\"ROLE_ADMIN\"]'),
(2, 'Jobs', 'Steve', '1234567891', 'sjobs@apple.com', '$2y$13$ZKewarWH.abxzXVYma9EA.ww4kQ0WR9dH5skHtgZMNESMvS3mXYzW', 1, '[]');

INSERT INTO `user_order` (`id`, `user_id`, `customer_address_id`, `number`, `is_validated`, `order_date`, `delivery_fee`) VALUES
(1, 1, 1, 1, 1, '2024-02-03 23:43:06', 5.00);

-- 2024-04-18 18:37:10
