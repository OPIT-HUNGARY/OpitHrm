<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131218150615 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_te_paid_expense (id INT AUTO_INCREMENT NOT NULL, expense_type_id INT DEFAULT NULL, date DATE NOT NULL, amount DOUBLE PRECISION NOT NULL, destination VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, travelExpense_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_4EEA2687A857C7A9 (expense_type_id), INDEX IDX_4EEA2687F788BDCA (travelExpense_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_te_paid_expense ADD CONSTRAINT FK_4EEA2687A857C7A9 FOREIGN KEY (expense_type_id) REFERENCES opithrm_te_expense_type (id)");
        $this->addSql("ALTER TABLE opithrm_te_paid_expense ADD CONSTRAINT FK_4EEA2687F788BDCA FOREIGN KEY (travelExpense_id) REFERENCES opithrm_travel_expense (id)");
        $this->addSql("DROP TABLE opithrm_te_paid_expenses");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_te_paid_expenses (id INT AUTO_INCREMENT NOT NULL, expense_type_id INT DEFAULT NULL, currency VARCHAR(30) NOT NULL, date DATE NOT NULL, excahnge_rate INT NOT NULL, amount INT NOT NULL, destination VARCHAR(255) NOT NULL, cost_huf INT NOT NULL, cost_euro INT NOT NULL, travelExpense_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, justification VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_689C33AEA857C7A9 (expense_type_id), INDEX IDX_689C33AEF788BDCA (travelExpense_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_te_paid_expenses ADD CONSTRAINT FK_689C33AEF788BDCA FOREIGN KEY (travelExpense_id) REFERENCES opithrm_travel_expense (id)");
        $this->addSql("ALTER TABLE opithrm_te_paid_expenses ADD CONSTRAINT FK_689C33AEA857C7A9 FOREIGN KEY (expense_type_id) REFERENCES opithrm_te_expense_type (id)");
        $this->addSql("DROP TABLE opithrm_te_paid_expense");
    }
}
