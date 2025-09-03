<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250903133156 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Seed products and promotions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
            INSERT INTO `products` (`sku`, `name`, `unit_price_cents`, `created_at`, `updated_at`)
            SELECT 'A', 'Item A', 5000, NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `sku` = 'A');
        SQL);

        $this->addSql(<<<SQL
            INSERT INTO `products` (`sku`, `name`, `unit_price_cents`, `created_at`, `updated_at`)
            SELECT 'B', 'Item B', 3000, NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `sku` = 'B');
        SQL);

        $this->addSql(<<<SQL
            INSERT INTO `products` (`sku`, `name`, `unit_price_cents`, `created_at`, `updated_at`)
            SELECT 'C', 'Item C', 2000, NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `sku` = 'C');
        SQL);

        $this->addSql(<<<SQL
            INSERT INTO `products` (`sku`, `name`, `unit_price_cents`, `created_at`, `updated_at`)
            SELECT 'D', 'Item D', 1000, NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM `products` WHERE `sku` = 'D');
        SQL);

        $this->addSql(<<<SQL
            INSERT INTO `promotions` (`product_id`, `type`, `n_qty`, `special_price_cents`, `created_at`, `updated_at`)
            SELECT p.`id`, 'n_for_price', 3, 13000, NOW(), NOW()
            FROM `products` p
            WHERE p.`sku` = 'A'
              AND NOT EXISTS (
                SELECT 1 FROM `promotions` pr
                WHERE pr.`product_id` = p.`id`
                  AND pr.`type` = 'n_for_price'
                  AND pr.`n_qty` = 3
                  AND pr.`special_price_cents` = 13000
            );
        SQL);

        $this->addSql(<<<SQL
            INSERT INTO `promotions` (`product_id`, `type`, `n_qty`, `special_price_cents`, `created_at`, `updated_at`)
            SELECT p.`id`, 'n_for_price', 2, 4500, NOW(), NOW()
            FROM `products` p
            WHERE p.`sku` = 'B'
              AND NOT EXISTS (
                SELECT 1 FROM `promotions` pr
                WHERE pr.`product_id` = p.`id`
                  AND pr.`type` = 'n_for_price'
                  AND pr.`n_qty` = 2
                  AND pr.`special_price_cents` = 4500
              );
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
            DELETE FROM `promotions` pr
            JOIN `products` p ON pr.`product_id` = p.`id`
            WHERE p.`sku` = 'A' AND pr.`type` = 'n_for_price' AND pr.`n_qty` = 3 AND pr.`special_price_cents` = 13000;
        SQL);

        $this->addSql(<<<SQL
            DELETE FROM `promotions` pr
            JOIN `products` p ON pr.`product_id` = p.`id`
            WHERE p.`sku` = 'B' AND pr.`type` = 'n_for_price' AND pr.`n_qty` = 2 AND pr.`special_price_cents` = 4500;
        SQL);
    }
}
