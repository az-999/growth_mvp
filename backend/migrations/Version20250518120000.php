<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250518120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add count and product_id to orders';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE orders ADD count INT NOT NULL DEFAULT 1, ADD product_id VARCHAR(64) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE orders DROP count, DROP product_id');
    }
}
