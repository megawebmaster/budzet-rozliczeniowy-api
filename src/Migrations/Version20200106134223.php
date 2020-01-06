<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200106134223 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE budget_receipt (id INT AUTO_INCREMENT NOT NULL, budget_year_id INT DEFAULT NULL, month INT NOT NULL, day INT DEFAULT NULL, creator_id VARCHAR(50) NOT NULL, expense_id INT NOT NULL, INDEX IDX_911AFCA48242B446 (budget_year_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_receipt_item (id INT AUTO_INCREMENT NOT NULL, receipt_id INT DEFAULT NULL, category_id INT DEFAULT NULL, value LONGTEXT NOT NULL, description LONGTEXT NOT NULL, creator_id VARCHAR(50) NOT NULL, INDEX IDX_72C258B12B5CA896 (receipt_id), INDEX IDX_72C258B112469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_receipt ADD CONSTRAINT FK_911AFCA48242B446 FOREIGN KEY (budget_year_id) REFERENCES budget_year (id)');
        $this->addSql('ALTER TABLE budget_receipt_item ADD CONSTRAINT FK_72C258B12B5CA896 FOREIGN KEY (receipt_id) REFERENCES budget_receipt (id)');
        $this->addSql('ALTER TABLE budget_receipt_item ADD CONSTRAINT FK_72C258B112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');

        $this->addSql('
            INSERT INTO budget_receipt (budget_year_id, month, day, creator_id, expense_id) 
            SELECT budget_year_id, month, day, creator_id, id FROM budget_expense
        ');
        $this->addSql('
            INSERT INTO budget_receipt_item (receipt_id, category_id, value, description, creator_id) 
            SELECT br.id, be.category_id, be.value, be.description, be.creator_id FROM budget_expense be 
                JOIN budget_receipt br ON br.expense_id = be.id
        ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('
            INSERT INTO budget_expense (budget_year_id, category_id, month, day, value, description, creator_id)
            SELECT br.budget_year_id, bri.category_id, br.month, br.day, bri.value, bri.description, br.creator_id
            FROM budget_receipt br
                JOIN budget_receipt_item bri ON br.id = bri.receipt_id
        ');
        $this->addSql('
            INSERT INTO budget_receipt_item (receipt_id, category_id, value, description, creator_id) 
            SELECT br.id, be.category_id, be.value, be.description, be.creator_id FROM budget_expense be 
                JOIN budget_receipt br ON br.expense_id = be.id
        ');

        $this->addSql('ALTER TABLE budget_receipt_item DROP FOREIGN KEY FK_72C258B12B5CA896');
        $this->addSql('DROP TABLE budget_receipt');
        $this->addSql('DROP TABLE budget_receipt_item');
    }
}
