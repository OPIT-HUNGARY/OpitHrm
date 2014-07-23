<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140619145049 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_applicants (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phoneNumber VARCHAR(255) NOT NULL, keywords VARCHAR(255) NOT NULL, cv VARCHAR(255) NOT NULL, applicationDate DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_job_position_applicants (jobposition_id INT NOT NULL, applicant_id INT NOT NULL, INDEX IDX_6C3D0CA6D4B84E23 (jobposition_id), INDEX IDX_6C3D0CA697139001 (applicant_id), PRIMARY KEY(jobposition_id, applicant_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_job_position_applicants ADD CONSTRAINT FK_6C3D0CA6D4B84E23 FOREIGN KEY (jobposition_id) REFERENCES opithrm_job_position (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE opithrm_job_position_applicants ADD CONSTRAINT FK_6C3D0CA697139001 FOREIGN KEY (applicant_id) REFERENCES opithrm_applicants (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_job_position_applicants DROP FOREIGN KEY FK_6C3D0CA697139001");
        $this->addSql("DROP TABLE opithrm_applicants");
        $this->addSql("DROP TABLE opithrm_job_position_applicants");
    }
}
