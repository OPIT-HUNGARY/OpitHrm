<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131218120813 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_travel_expense (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, rechargeable TINYINT(1) NOT NULL, departure_date_time DATETIME NOT NULL, arrival_date_time DATETIME NOT NULL, departure_country VARCHAR(30) NOT NULL, arrival_country VARCHAR(30) NOT NULL, advances_recieved TINYINT(1) NOT NULL, advances_payback DOUBLE PRECISION NOT NULL, to_settle DOUBLE PRECISION NOT NULL, pay_in_euro TINYINT(1) NOT NULL, bank_account_number VARCHAR(50) NOT NULL, bank_name VARCHAR(30) NOT NULL, tax_identification INT NOT NULL, INDEX IDX_45CCB7FDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_te_expense_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_te_paid_expenses (id INT AUTO_INCREMENT NOT NULL, expense_type_id INT DEFAULT NULL, currency VARCHAR(30) NOT NULL, date DATE NOT NULL, excahnge_rate INT NOT NULL, amount INT NOT NULL, destination VARCHAR(255) NOT NULL, cost_huf INT NOT NULL, cost_euro INT NOT NULL, travelExpense_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, justification VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_689C33AEA857C7A9 (expense_type_id), INDEX IDX_689C33AEF788BDCA (travelExpense_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_travel_expense ADD CONSTRAINT FK_45CCB7FDA76ED395 FOREIGN KEY (user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_te_paid_expenses ADD CONSTRAINT FK_689C33AEA857C7A9 FOREIGN KEY (expense_type_id) REFERENCES opithrm_te_expense_type (id)");
        $this->addSql("ALTER TABLE opithrm_te_paid_expenses ADD CONSTRAINT FK_689C33AEF788BDCA FOREIGN KEY (travelExpense_id) REFERENCES opithrm_travel_expense (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_te_paid_expenses DROP FOREIGN KEY FK_689C33AEF788BDCA");
        $this->addSql("ALTER TABLE opithrm_te_paid_expenses DROP FOREIGN KEY FK_689C33AEA857C7A9");
        $this->addSql("DROP TABLE opithrm_travel_expense");
        $this->addSql("DROP TABLE opithrm_te_expense_type");
        $this->addSql("DROP TABLE opithrm_te_paid_expenses");
    }
}
