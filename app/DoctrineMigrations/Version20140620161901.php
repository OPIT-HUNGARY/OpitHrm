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
        
        $this->addSql("DROP TABLE notes_job_position_applicants");
        $this->addSql("ALTER TABLE notes_applicants ADD created_user_id INT DEFAULT NULL, ADD updated_user_id INT DEFAULT NULL, ADD job_position_id INT DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, ADD system TINYINT(1) DEFAULT '0' NOT NULL, ADD cvFile VARCHAR(255) DEFAULT NULL, CHANGE cv cv VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_applicants ADD CONSTRAINT FK_27663B5FE104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_applicants ADD CONSTRAINT FK_27663B5FBB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_applicants ADD CONSTRAINT FK_27663B5FBEE8350F FOREIGN KEY (job_position_id) REFERENCES notes_job_position (id)");
        $this->addSql("CREATE INDEX IDX_27663B5FE104C1D3 ON notes_applicants (created_user_id)");
        $this->addSql("CREATE INDEX IDX_27663B5FBB649746 ON notes_applicants (updated_user_id)");
        $this->addSql("CREATE INDEX IDX_27663B5FBEE8350F ON notes_applicants (job_position_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_job_position_applicants (jobposition_id INT NOT NULL, applicant_id INT NOT NULL, INDEX IDX_6C3D0CA6D4B84E23 (jobposition_id), INDEX IDX_6C3D0CA697139001 (applicant_id), PRIMARY KEY(jobposition_id, applicant_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_job_position_applicants ADD CONSTRAINT FK_6C3D0CA697139001 FOREIGN KEY (applicant_id) REFERENCES notes_applicants (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE notes_job_position_applicants ADD CONSTRAINT FK_6C3D0CA6D4B84E23 FOREIGN KEY (jobposition_id) REFERENCES notes_job_position (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE notes_applicants DROP FOREIGN KEY FK_27663B5FE104C1D3");
        $this->addSql("ALTER TABLE notes_applicants DROP FOREIGN KEY FK_27663B5FBB649746");
        $this->addSql("ALTER TABLE notes_applicants DROP FOREIGN KEY FK_27663B5FBEE8350F");
        $this->addSql("DROP INDEX IDX_27663B5FE104C1D3 ON notes_applicants");
        $this->addSql("DROP INDEX IDX_27663B5FBB649746 ON notes_applicants");
        $this->addSql("DROP INDEX IDX_27663B5FBEE8350F ON notes_applicants");
        $this->addSql("ALTER TABLE notes_applicants DROP created_user_id, DROP updated_user_id, DROP job_position_id, DROP created, DROP updated, DROP system, DROP cvFile, CHANGE cv cv VARCHAR(255) NOT NULL");
    }
}
