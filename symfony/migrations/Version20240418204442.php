<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418204442 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address_type (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_BA388B7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_product_quantity (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, cart_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_E987F83B4584665A (product_id), INDEX IDX_E987F83B1AD5CDBF (cart_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, media_id INT DEFAULT NULL, name VARCHAR(128) NOT NULL, description VARCHAR(512) DEFAULT NULL, INDEX IDX_64C19C1EA9FDD75 (media_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer_address (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, user_id INT NOT NULL, last_name VARCHAR(128) NOT NULL, first_name VARCHAR(128) NOT NULL, phone VARCHAR(12) NOT NULL, address VARCHAR(512) NOT NULL, postal_code VARCHAR(7) NOT NULL, city VARCHAR(256) NOT NULL, country VARCHAR(64) NOT NULL, INDEX IDX_1193CB3FC54C8C93 (type_id), INDEX IDX_1193CB3FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, path VARCHAR(256) NOT NULL, alt VARCHAR(256) DEFAULT NULL, type VARCHAR(256) NOT NULL, INDEX IDX_6A2CA10C4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_product_quantity (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, original_order_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_365568BD4584665A (product_id), INDEX IDX_365568BDAEC08A29 (original_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(256) NOT NULL, description VARCHAR(512) DEFAULT NULL, price DOUBLE PRECISION NOT NULL, available TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(128) NOT NULL, first_name VARCHAR(128) NOT NULL, phone VARCHAR(12) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) DEFAULT 0 NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_order (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, customer_address_id INT NOT NULL, number INT NOT NULL, is_validated TINYINT(1) DEFAULT 0 NOT NULL, order_date DATETIME NOT NULL, delivery_fee NUMERIC(6, 2) NOT NULL, INDEX IDX_17EB68C0A76ED395 (user_id), INDEX IDX_17EB68C087EABF7 (customer_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart_product_quantity ADD CONSTRAINT FK_E987F83B4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE cart_product_quantity ADD CONSTRAINT FK_E987F83B1AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE customer_address ADD CONSTRAINT FK_1193CB3FC54C8C93 FOREIGN KEY (type_id) REFERENCES address_type (id)');
        $this->addSql('ALTER TABLE customer_address ADD CONSTRAINT FK_1193CB3FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_product_quantity ADD CONSTRAINT FK_365568BD4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE order_product_quantity ADD CONSTRAINT FK_365568BDAEC08A29 FOREIGN KEY (original_order_id) REFERENCES user_order (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE user_order ADD CONSTRAINT FK_17EB68C0A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE user_order ADD CONSTRAINT FK_17EB68C087EABF7 FOREIGN KEY (customer_address_id) REFERENCES customer_address (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart DROP FOREIGN KEY FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart_product_quantity DROP FOREIGN KEY FK_E987F83B4584665A');
        $this->addSql('ALTER TABLE cart_product_quantity DROP FOREIGN KEY FK_E987F83B1AD5CDBF');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1EA9FDD75');
        $this->addSql('ALTER TABLE customer_address DROP FOREIGN KEY FK_1193CB3FC54C8C93');
        $this->addSql('ALTER TABLE customer_address DROP FOREIGN KEY FK_1193CB3FA76ED395');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C4584665A');
        $this->addSql('ALTER TABLE order_product_quantity DROP FOREIGN KEY FK_365568BD4584665A');
        $this->addSql('ALTER TABLE order_product_quantity DROP FOREIGN KEY FK_365568BDAEC08A29');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE user_order DROP FOREIGN KEY FK_17EB68C0A76ED395');
        $this->addSql('ALTER TABLE user_order DROP FOREIGN KEY FK_17EB68C087EABF7');
        $this->addSql('DROP TABLE address_type');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_product_quantity');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE customer_address');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE order_product_quantity');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_order');
    }
}
