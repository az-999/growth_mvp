<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240518120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for Growth MVP';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE shops (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, shop_id INT NOT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), INDEX IDX_1483A5E94D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE orders (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, number VARCHAR(64) NOT NULL, total NUMERIC(12, 2) NOT NULL, customer_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_E52FFDEE4D16C4DD (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE telegram_integrations (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, bot_token_encrypted LONGTEXT NOT NULL, chat_id VARCHAR(64) NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_shop (shop_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE telegram_send_log (id INT AUTO_INCREMENT NOT NULL, shop_id INT NOT NULL, order_id INT NOT NULL, message LONGTEXT NOT NULL, status VARCHAR(16) NOT NULL, error LONGTEXT DEFAULT NULL, sent_at DATETIME NOT NULL, UNIQUE INDEX uniq_shop_order (shop_id, order_id), INDEX IDX_shop (shop_id), INDEX IDX_order (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_users_shop FOREIGN KEY (shop_id) REFERENCES shops (id)');
        $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_orders_shop FOREIGN KEY (shop_id) REFERENCES shops (id)');
        $this->addSql('ALTER TABLE telegram_integrations ADD CONSTRAINT FK_tg_shop FOREIGN KEY (shop_id) REFERENCES shops (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE telegram_send_log ADD CONSTRAINT FK_log_shop FOREIGN KEY (shop_id) REFERENCES shops (id)');
        $this->addSql('ALTER TABLE telegram_send_log ADD CONSTRAINT FK_log_order FOREIGN KEY (order_id) REFERENCES orders (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE telegram_send_log DROP FOREIGN KEY FK_log_shop');
        $this->addSql('ALTER TABLE telegram_send_log DROP FOREIGN KEY FK_log_order');
        $this->addSql('ALTER TABLE telegram_integrations DROP FOREIGN KEY FK_tg_shop');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_orders_shop');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_users_shop');
        $this->addSql('DROP TABLE telegram_send_log');
        $this->addSql('DROP TABLE telegram_integrations');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE shops');
    }
}
