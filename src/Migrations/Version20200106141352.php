<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200106141352 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE budget_expense');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE budget_expense (id INT AUTO_INCREMENT NOT NULL, budget_year_id INT DEFAULT NULL, category_id INT DEFAULT NULL, month INT NOT NULL, day INT DEFAULT NULL, value LONGTEXT NOT NULL, description LONGTEXT NOT NULL, creator_id VARCHAR(50) NOT NULL, INDEX IDX_EFB9C7478242B446 (budget_year_id), INDEX IDX_EFB9C74712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C7478242B446 FOREIGN KEY (budget_year_id) REFERENCES budget_year (id)');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C74712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }
}
