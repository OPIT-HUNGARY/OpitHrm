<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140620161901 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE opithrm_job_position_applicants");
        $this->addSql("ALTER TABLE opithrm_applicants ADD created_user_id INT DEFAULT NULL, ADD updated_user_id INT DEFAULT NULL, ADD job_position_id INT DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, ADD system TINYINT(1) DEFAULT '0' NOT NULL, ADD cvFile VARCHAR(255) DEFAULT NULL, CHANGE cv cv VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_applicants ADD CONSTRAINT FK_27663B5FE104C1D3 FOREIGN KEY (created_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_applicants ADD CONSTRAINT FK_27663B5FBB649746 FOREIGN KEY (updated_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_applicants ADD CONSTRAINT FK_27663B5FBEE8350F FOREIGN KEY (job_position_id) REFERENCES opithrm_job_position (id)");
        $this->addSql("CREATE INDEX IDX_27663B5FE104C1D3 ON opithrm_applicants (created_user_id)");
        $this->addSql("CREATE INDEX IDX_27663B5FBB649746 ON opithrm_applicants (updated_user_id)");
        $this->addSql("CREATE INDEX IDX_27663B5FBEE8350F ON opithrm_applicants (job_position_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_job_position_applicants (jobposition_id INT NOT NULL, applicant_id INT NOT NULL, INDEX IDX_6C3D0CA6D4B84E23 (jobposition_id), INDEX IDX_6C3D0CA697139001 (applicant_id), PRIMARY KEY(jobposition_id, applicant_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_job_position_applicants ADD CONSTRAINT FK_6C3D0CA697139001 FOREIGN KEY (applicant_id) REFERENCES opithrm_applicants (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE opithrm_job_position_applicants ADD CONSTRAINT FK_6C3D0CA6D4B84E23 FOREIGN KEY (jobposition_id) REFERENCES opithrm_job_position (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE opithrm_applicants DROP FOREIGN KEY FK_27663B5FE104C1D3");
        $this->addSql("ALTER TABLE opithrm_applicants DROP FOREIGN KEY FK_27663B5FBB649746");
        $this->addSql("ALTER TABLE opithrm_applicants DROP FOREIGN KEY FK_27663B5FBEE8350F");
        $this->addSql("DROP INDEX IDX_27663B5FE104C1D3 ON opithrm_applicants");
        $this->addSql("DROP INDEX IDX_27663B5FBB649746 ON opithrm_applicants");
        $this->addSql("DROP INDEX IDX_27663B5FBEE8350F ON opithrm_applicants");
        $this->addSql("ALTER TABLE opithrm_applicants DROP created_user_id, DROP updated_user_id, DROP job_position_id, DROP created, DROP updated, DROP system, DROP cvFile, CHANGE cv cv VARCHAR(255) NOT NULL");
    }
}
