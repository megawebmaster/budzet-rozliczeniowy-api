<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180513094553 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget_entry DROP FOREIGN KEY FK_4030088612469DE2');
        $this->addSql('ALTER TABLE budget_entry ADD CONSTRAINT FK_4030088612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE budget_expense DROP FOREIGN KEY FK_EFB9C74712469DE2');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C74712469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE budget_entry DROP FOREIGN KEY FK_4030088612469DE2');
        $this->addSql('ALTER TABLE budget_entry ADD CONSTRAINT FK_4030088612469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE budget_expense DROP FOREIGN KEY FK_EFB9C74712469DE2');
        $this->addSql('ALTER TABLE budget_expense ADD CONSTRAINT FK_EFB9C74712469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }
}
