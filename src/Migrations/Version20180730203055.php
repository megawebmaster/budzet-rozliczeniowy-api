<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180730203055 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE budget_access (id INT AUTO_INCREMENT NOT NULL, budget_id INT NOT NULL, user_id VARCHAR(255) DEFAULT NULL, recipient VARCHAR(255) DEFAULT NULL, name VARCHAR(50) DEFAULT NULL, slug VARCHAR(50) DEFAULT NULL, is_default TINYINT(1) NOT NULL, abilities TEXT DEFAULT NULL, INDEX IDX_841708AC36ABA6B8 (budget_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_access ADD CONSTRAINT FK_841708AC36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('INSERT INTO budget_access (`budget_id`, `user_id`, `name`, `slug`, `is_default`) SELECT b.id, b.user_id, b.name, b.slug, b.is_default FROM budget b');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE budget_access');
    }
}
