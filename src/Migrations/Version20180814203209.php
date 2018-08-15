<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180814203209 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget_access CHANGE recipient recipient LONGTEXT NOT NULL, CHANGE is_default is_default TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE budget_access RENAME INDEX idx_841708ac36aba6b8 TO IDX_52DC6DE836ABA6B8');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget_access CHANGE is_default is_default TINYINT(1) NOT NULL, CHANGE recipient recipient VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE budget_access RENAME INDEX idx_52dc6de836aba6b8 TO IDX_841708AC36ABA6B8');
    }
}
