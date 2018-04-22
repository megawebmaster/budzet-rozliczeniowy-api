<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180422190051 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_expense_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_year_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, budget_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, creator_id VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_64C19C136ABA6B8 ON category (budget_id)');
        $this->addSql('CREATE INDEX IDX_64C19C1727ACA70 ON category (parent_id)');
        $this->addSql('CREATE TABLE budget (id INT NOT NULL, name VARCHAR(50) NOT NULL, user_id VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE budget_expense (id INT NOT NULL, budget_year_id INT DEFAULT NULL, category_id INT DEFAULT NULL, month INT NOT NULL, day INT NOT NULL, value NUMERIC(8, 2) NOT NULL, description TEXT NOT NULL, creator_id VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EFB9C7478242B446 ON budget_expense (budget_year_id)');
        $this->addSql('CREATE INDEX IDX_EFB9C74712469DE2 ON budget_expense (category_id)');
        $this->addSql('CREATE TABLE budget_year (id INT NOT NULL, budget_id INT DEFAULT NULL, year INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C146716736ABA6B8 ON budget_year (budget_id)');
        $this->addSql('CREATE TABLE budget_entry (id INT NOT NULL, budget_year_id INT DEFAULT NULL, category_id INT DEFAULT NULL, month INT DEFAULT NULL, plan NUMERIC(8, 2) NOT NULL, real NUMERIC(8, 2) NOT NULL, creator_id VARCHAR(50) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_403008868242B446 ON budget_entry (budget_year_id)');
        $this->addSql('CREATE INDEX IDX_4030088612469DE2 ON budget_entry (category_id)');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C136ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C7478242B446 FOREIGN KEY (budget_year_id) REFERENCES budget_year (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C74712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_year ADD CONSTRAINT FK_C146716736ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_entry ADD CONSTRAINT FK_403008868242B446 FOREIGN KEY (budget_year_id) REFERENCES budget_year (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_entry ADD CONSTRAINT FK_4030088612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE budget_expense DROP CONSTRAINT FK_EFB9C74712469DE2');
        $this->addSql('ALTER TABLE budget_entry DROP CONSTRAINT FK_4030088612469DE2');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C136ABA6B8');
        $this->addSql('ALTER TABLE budget_year DROP CONSTRAINT FK_C146716736ABA6B8');
        $this->addSql('ALTER TABLE budget_expense DROP CONSTRAINT FK_EFB9C7478242B446');
        $this->addSql('ALTER TABLE budget_entry DROP CONSTRAINT FK_403008868242B446');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_expense_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_year_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_entry_id_seq CASCADE');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE budget_expense');
        $this->addSql('DROP TABLE budget_year');
        $this->addSql('DROP TABLE budget_entry');
    }
}
