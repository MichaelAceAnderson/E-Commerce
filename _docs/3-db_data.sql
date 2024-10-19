-- Adminer 4.8.1 MySQL 11.3.2-MariaDB-1:11.3.2+maria~ubu2204-log dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

USE `ace-su_ecommerce`;

SET NAMES utf8mb4;

INSERT INTO `address_type` (`id`, `type`) VALUES
(1,	'Entreprise'),
(2,	'Particulier'),
(3,	'Autre');

INSERT INTO `cart` (`id`, `user_id`) VALUES
(1,	1);

INSERT INTO `cart_product_quantity` (`id`, `product_id`, `cart_id`, `quantity`) VALUES
(1,	1,	1,	1),
(2,	2,	1,	3),
(3,	3,	1,	2);

INSERT INTO `category` (`id`, `media_id`, `name`, `description`) VALUES
(1,	NULL,	'Accessoires',	'Montres, lunettes de soleil, ceintures, cravates et noeuds papillon'),
(2,	NULL,	'Blousons',	'Vestes, manteaux, parkas'),
(3,	NULL,	'T-shirts',	'T-shirts, henleys, cols ronds, cols en V,...'),
(4,	NULL,	'Jeans',	'Jeans, pantalons en denim'),
(5,	NULL,	'Vestes de costume',	'Blazers, sportscoat et pièces de costume'),
(6,	NULL,	'Hauts de survêtement',	'Vestes de survêtement/jogging'),
(7,	NULL,	'Robes',	'Robes de soirée, robes de cocktail, robes de mariée'),
(8,	NULL,	'Chemises',	'Chemises habillées, décontractées, en jean,...'),
(9,	NULL,	'Pulls',	'Pulls, cardigans, sweaters'),
(10,	NULL,	'Polos',	'Polos'),
(11,	NULL,	'Pantalons de costume',	'Pantalons de costume, pantalons habillés'),
(12,	NULL,	'Jupes',	'Jupes'),
(13,	NULL,	'Pantalons de survêtement',	'Pantalons de survêtement/jogging'),
(14,	NULL,	'Chaussures habillées',	'Chaussures de ville, chaussures de cérémonie'),
(15,	NULL,	'Chaussures à talon',	'Escarpins, sandales à talon'),
(16,	NULL,	'Baskets',	'Baskets, sneakers'),
(17,	NULL,	'Chaussures de sport',	'Chaussures de running, chaussures de fitness');

INSERT INTO `customer_address` (`id`, `type_id`, `user_id`, `last_name`, `first_name`, `phone`, `address`, `postal_code`, `city`, `country`) VALUES
(1,	3,	1,	'Gates',	'Bill',	'1234567890',	'1, Microsoft Way',	'98052',	'Redmond',	'United States'),
(2,	3,	2,	'Jobs',	'Steve',	'1234567890',	'1, Infinite Loop',	'95014',	'Cupertino',	'United States');

INSERT INTO `media` (`id`, `product_id`, `path`, `alt`, `type`) VALUES
(1,	1,	'/uploads/media/unisex-accessories-sunglasses-wayfarer-black.png',	'Lunettes de soleil Wayfarer Noir',	'image'),
(2,	2,	'/uploads/media/unisex-accessories-sunglasses-wayfarer-havana.png',	'Lunettes de soleil Wayfarer Havanne',	'image'),
(3,	3,	'/uploads/media/man-jackets-teddy.png',	'Veste Teddy Homme',	'image'),
(4,	4,	'/uploads/media/man-t_shirts-round_neck.png',	'T-shirt à col rond Homme',	'image'),
(5,	5,	'/uploads/media/man-t_shirts-v_neck.png',	'T-shirt à col V Homme',	'image'),
(6,	6,	'/uploads/media/unisex-t_shirts-logo-sunglasses.png',	'T-shirt à motif lunettes de soleil Unisexe',	'image'),
(7,	7,	'/uploads/media/man-t_shirts-henley.png',	'T-shirt Henley Homme',	'image'),
(8,	8,	'/uploads/media/man-jeans-straight.png',	'Jean droit Homme',	'image'),
(9,	9,	'/uploads/media/woman-t_shirts-round_neck.png',	'T-shirt à col rond Femme',	'image'),
(10,	10,	'/uploads/media/woman-t_shirts-v_neck.png',	'T-shirt col V Femme',	'image');

INSERT INTO `order_product_quantity` (`id`, `product_id`, `original_order_id`, `quantity`) VALUES
(1,	1,	1,	1),
(2,	2,	1,	3),
(3,	3,	1,	2);

INSERT INTO `product` (`id`, `category_id`, `name`, `description`, `price`, `available`) VALUES
(1,	1,	'Lunettes de soleil Wayfarer noires',	'Lunettes de soleil Wayfarer noires',	150,	1),
(2,	1,	'Lunettes de soleil Wayfarer havanne',	'Lunettes de soleil Wayfarer havanne',	150,	1),
(3,	2,	'Veste Teddy Homme',	'Veste Teddy Homme',	50,	1),
(4,	3,	'T-shirt à col rond Homme',	'T-shirt à col rond Homme',	5,	1),
(5,	3,	'T-shirt à col V Homme',	'T-shirt à col V Homme',	5,	1),
(6,	1,	'T-shirt à motif lunettes de soleil',	'T-shirt à motif lunettes de soleil',	6,	1),
(7,	3,	'T-shirt Henley Homme',	'T-shirt Henley Homme',	6,	1),
(8,	4,	'Jean droit Homme',	'Jean droit Homme',	20,	1),
(9,	3,	'T-shirt à col rond Femme',	'T-shirt à col rond Femme',	5,	1),
(10,	3,	'T-shirt à col V Femme',	'T-shirt à col V Femme',	5,	1);

INSERT INTO `user` (`id`, `last_name`, `first_name`, `phone`, `email`, `password`, `is_verified`, `roles`) VALUES
(1,	'Gates',	'Bill',	'1234567890',	'bgates@microsoft.com',	'$2y$13$kDKX.7WfhndZwWSsgQYi2Oq7SAQoveMiY/ghxuNjT16L/VL4QcP3e',	1,	'[\"ROLE_ADMIN\"]'),
(2,	'Jobs',	'Steve',	'1234567891',	'sjobs@apple.com',	'$2y$13$ZKewarWH.abxzXVYma9EA.ww4kQ0WR9dH5skHtgZMNESMvS3mXYzW',	1,	'[]');

INSERT INTO `user_order` (`id`, `user_id`, `customer_address_id`, `number`, `is_validated`, `order_date`, `delivery_fee`) VALUES
(1,	1,	1,	1,	1,	'2024-02-03 23:43:06',	5.00);

-- 2024-04-18 18:37:10