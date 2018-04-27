<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180423173439 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER SEQUENCE budget_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE category_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_year_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_entry_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_expense_id_seq INCREMENT BY 1');
        $this->addSql('ALTER TABLE budget ADD is_default BOOLEAN DEFAULT \'false\' NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER SEQUENCE category_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_expense_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_year_id_seq INCREMENT BY 1');
        $this->addSql('ALTER SEQUENCE budget_entry_id_seq INCREMENT BY 1');
        $this->addSql('ALTER TABLE budget DROP is_default');
    }
}
