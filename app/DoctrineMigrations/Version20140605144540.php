<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140605144540 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_users DROP FOREIGN KEY FK_8E744D496DD822C6");
        $this->addSql("DROP INDEX IDX_8E744D496DD822C6 ON opithrm_users");
        $this->addSql("ALTER TABLE opithrm_employees ADD job_title_id INT DEFAULT NULL, ADD bank_account_number VARCHAR(50) NOT NULL, ADD bank_name VARCHAR(30) NOT NULL, ADD tax_identification BIGINT DEFAULT NULL, ADD entitled_leaves INT DEFAULT NULL");
        
        // Migrate data from user to employee attributes
        $this->addSql("UPDATE opithrm_employees e, opithrm_users u SET e.job_title_id = u.job_title_id, e.bank_account_number = u.bank_account_number, e.bank_name = u.bank_name, e.tax_identification = u.tax_identification, e.entitled_leaves = u.entitled_leaves WHERE u.employee_id = e.id");
        
        $this->addSql("ALTER TABLE opithrm_users DROP job_title_id, DROP bank_account_number, DROP bank_name, DROP tax_identification, DROP entitled_leaves");
        $this->addSql("ALTER TABLE opithrm_employees ADD CONSTRAINT FK_1B3F563D6DD822C6 FOREIGN KEY (job_title_id) REFERENCES opithrm_job_titles (id)");
        $this->addSql("CREATE INDEX IDX_1B3F563D6DD822C6 ON opithrm_employees (job_title_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_employees DROP FOREIGN KEY FK_1B3F563D6DD822C6");
        $this->addSql("DROP INDEX IDX_1B3F563D6DD822C6 ON opithrm_employees");
        $this->addSql("ALTER TABLE opithrm_users ADD job_title_id INT DEFAULT NULL, ADD bank_account_number VARCHAR(50) NOT NULL, ADD bank_name VARCHAR(30) NOT NULL, ADD tax_identification BIGINT DEFAULT NULL, ADD entitled_leaves INT DEFAULT NULL");
        
        // Migrate data from employee to user attributes
        $this->addSql("UPDATE opithrm_users u, opithrm_employees e SET u.job_title_id = e.job_title_id, u.bank_account_number = e.bank_account_number, u.bank_name = e.bank_name, u.tax_identification = e.tax_identification, u.entitled_leaves = e.entitled_leaves WHERE u.employee_id = e.id");
        
        $this->addSql("ALTER TABLE opithrm_employees DROP job_title_id, DROP bank_account_number, DROP bank_name, DROP tax_identification, DROP entitled_leaves");
        $this->addSql("ALTER TABLE opithrm_users ADD CONSTRAINT FK_8E744D496DD822C6 FOREIGN KEY (job_title_id) REFERENCES opithrm_job_titles (id)");
        $this->addSql("CREATE INDEX IDX_8E744D496DD822C6 ON opithrm_users (job_title_id)");
    }
}
