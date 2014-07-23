<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140108145331 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE opithrm_status (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_AD4C311C5E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_status_workflow (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, status_id INT DEFAULT NULL, INDEX IDX_6EBF22CD727ACA70 (parent_id), INDEX IDX_6EBF22CD6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_states_travel_requests (id INT AUTO_INCREMENT NOT NULL, travel_request_id INT DEFAULT NULL, status_id INT DEFAULT NULL, INDEX IDX_49A1F1CF1BCB5976 (travel_request_id), INDEX IDX_49A1F1CF6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_states_travel_expense (id INT AUTO_INCREMENT NOT NULL, travel_expense_id INT DEFAULT NULL, status_id INT DEFAULT NULL, INDEX IDX_D171F08AAA203AA8 (travel_expense_id), INDEX IDX_D171F08A6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_status_workflow ADD CONSTRAINT FK_6EBF22CD727ACA70 FOREIGN KEY (parent_id) REFERENCES opithrm_status (id)");
        $this->addSql("ALTER TABLE opithrm_status_workflow ADD CONSTRAINT FK_6EBF22CD6BF700BD FOREIGN KEY (status_id) REFERENCES opithrm_status (id)");
        $this->addSql("ALTER TABLE opithrm_states_travel_requests ADD CONSTRAINT FK_49A1F1CF1BCB5976 FOREIGN KEY (travel_request_id) REFERENCES opithrm_travel_request (id)");
        $this->addSql("ALTER TABLE opithrm_states_travel_requests ADD CONSTRAINT FK_49A1F1CF6BF700BD FOREIGN KEY (status_id) REFERENCES opithrm_status (id)");
        $this->addSql("ALTER TABLE opithrm_states_travel_expense ADD CONSTRAINT FK_D171F08AAA203AA8 FOREIGN KEY (travel_expense_id) REFERENCES opithrm_travel_expense (id)");
        $this->addSql("ALTER TABLE opithrm_states_travel_expense ADD CONSTRAINT FK_D171F08A6BF700BD FOREIGN KEY (status_id) REFERENCES opithrm_status (id)");

        // Insert status and status workflow
        $this->addSql("INSERT INTO opithrm_status(id, name) VALUES (4,'Approved'),(1,'Created'),(2,'For Approval'),(6,'Paid'),(5,'Rejected'),(3,'Revise')");
        $this->addSql("INSERT INTO opithrm_status_workflow(id, parent_id, status_id) VALUES (1,NULL,1),(2,1,2),(3,2,3),(4,2,4),(5,3,2),(6,2,5),(7,4,6)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_status_workflow DROP FOREIGN KEY FK_6EBF22CD727ACA70");
        $this->addSql("ALTER TABLE opithrm_status_workflow DROP FOREIGN KEY FK_6EBF22CD6BF700BD");
        $this->addSql("ALTER TABLE opithrm_states_travel_requests DROP FOREIGN KEY FK_49A1F1CF6BF700BD");
        $this->addSql("ALTER TABLE opithrm_states_travel_expense DROP FOREIGN KEY FK_D171F08A6BF700BD");
        $this->addSql("DROP TABLE opithrm_status");
        $this->addSql("DROP TABLE opithrm_status_workflow");
        $this->addSql("DROP TABLE opithrm_states_travel_requests");
        $this->addSql("DROP TABLE opithrm_states_travel_expense");
    }
}
