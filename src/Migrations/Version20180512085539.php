<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180512085539 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, budget_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, name LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, started_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, creator_id VARCHAR(50) NOT NULL, INDEX IDX_64C19C136ABA6B8 (budget_id), INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(50) NOT NULL, name VARCHAR(50) NOT NULL, is_default TINYINT(1) DEFAULT \'0\' NOT NULL, user_id VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_expense (id INT AUTO_INCREMENT NOT NULL, budget_year_id INT DEFAULT NULL, category_id INT DEFAULT NULL, month INT NOT NULL, day INT DEFAULT NULL, value LONGTEXT NOT NULL, description LONGTEXT NOT NULL, creator_id VARCHAR(50) NOT NULL, INDEX IDX_EFB9C7478242B446 (budget_year_id), INDEX IDX_EFB9C74712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_year (id INT AUTO_INCREMENT NOT NULL, budget_id INT DEFAULT NULL, year INT NOT NULL, INDEX IDX_C146716736ABA6B8 (budget_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_entry (id INT AUTO_INCREMENT NOT NULL, budget_year_id INT DEFAULT NULL, category_id INT DEFAULT NULL, month INT DEFAULT NULL, planned_value LONGTEXT NOT NULL, real_value LONGTEXT NOT NULL, creator_id VARCHAR(50) NOT NULL, INDEX IDX_403008868242B446 (budget_year_id), INDEX IDX_4030088612469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C7478242B446 FOREIGN KEY (budget_year_id) REFERENCES budget_year (id)');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C74712469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE budget_year ADD CONSTRAINT FK_C146716736ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id)');
        $this->addSql('ALTER TABLE budget_entry ADD CONSTRAINT FK_403008868242B446 FOREIGN KEY (budget_year_id) REFERENCES budget_year (id)');
        $this->addSql('ALTER TABLE budget_entry ADD CONSTRAINT FK_4030088612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE budget_expense DROP FOREIGN KEY FK_EFB9C74712469DE2');
        $this->addSql('ALTER TABLE budget_entry DROP FOREIGN KEY FK_4030088612469DE2');
        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C136ABA6B8');
        $this->addSql('ALTER TABLE budget_year DROP FOREIGN KEY FK_C146716736ABA6B8');
        $this->addSql('ALTER TABLE budget_expense DROP FOREIGN KEY FK_EFB9C7478242B446');
        $this->addSql('ALTER TABLE budget_entry DROP FOREIGN KEY FK_403008868242B446');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE budget_expense');
        $this->addSql('DROP TABLE budget_year');
        $this->addSql('DROP TABLE budget_entry');
    }
}
