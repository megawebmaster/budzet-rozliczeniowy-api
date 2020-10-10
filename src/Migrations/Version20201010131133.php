<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201010131133 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category ADD web_crypto TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE budget_entry ADD web_crypto TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE budget_receipt ADD web_crypto TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE budget_receipt_item ADD web_crypto TINYINT(1) NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget_entry DROP web_crypto');
        $this->addSql('ALTER TABLE budget_receipt DROP web_crypto');
        $this->addSql('ALTER TABLE budget_receipt_item DROP web_crypto');
        $this->addSql('ALTER TABLE category DROP web_crypto');
    }
}
