<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903133119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Setup project schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `products` (
            `id` INT AUTO_INCREMENT NOT NULL,
            `sku` VARCHAR(64) NOT NULL,
            `name` VARCHAR(255) NOT NULL,
            `unit_price_cents` INT NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            UNIQUE KEY `unique_products_sku` (`sku`),
            PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `orders` (
            `id` INT AUTO_INCREMENT NOT NULL,
            `status` VARCHAR(32) NOT NULL,
            `total_cents` INT NOT NULL,
            `currency` CHAR(3) NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE order_items (
            `id` INT AUTO_INCREMENT NOT NULL,
            `order_id` INT NOT NULL,
            `product_id` INT DEFAULT NULL,
            `sku` VARCHAR(64) NOT NULL,
            `product_name` VARCHAR(255) NOT NULL,
            `qty` INT NOT NULL,
            `unit_price_cents` INT NOT NULL,
            `bundle_count` INT NOT NULL,
            `bundle_price_cents` INT DEFAULT NULL,
            `line_total_cents` INT NOT NULL,
            `currency` CHAR(3) NOT NULL,
            `created_at` DATETIME NOT NULL,
            INDEX `idx_order_id` (`order_id`),
            INDEX `idx_product_id` (`product_id`),
            INDEX `idx_sku` (`sku`),
            CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
            PRIMARY KEY(`id`)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `promotions` (
            `id` INT AUTO_INCREMENT NOT NULL,
            `product_id` INT NOT NULL,
            `type` VARCHAR(32) NOT NULL,
            `n_qty` INT NOT NULL,
            `special_price_cents` INT NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NOT NULL,
            INDEX `idx_promo_product` (`product_id`),
            UNIQUE KEY `promotions_product_type_qty` (`product_id`, `type`, `n_qty`),
            CONSTRAINT `fk_promotions_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `promotions` DROP FOREIGN KEY `fk_promotions_product`');
        $this->addSql('ALTER TABLE `order_items` DROP FOREIGN KEY `fk_order_items_order`');
        $this->addSql('ALTER TABLE `order_items` DROP FOREIGN KEY `fk_order_items_product`');

        $this->addSql('DROP TABLE `order_items`');
        $this->addSql('DROP TABLE `promotions`');
        $this->addSql('DROP TABLE `orders`');
        $this->addSql('DROP TABLE `products`');
    }
}
