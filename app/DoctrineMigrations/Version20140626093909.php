<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140626093909 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE notes_applicant_comments (id INT NOT NULL, states_applicant_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_B075E80C19CF17B1 (states_applicant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_applicant_tokens (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, applicant_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_states_applicants (id INT AUTO_INCREMENT NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, applicant_id INT DEFAULT NULL, status_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, system TINYINT(1) DEFAULT '0' NOT NULL, INDEX IDX_82D0213DE104C1D3 (created_user_id), INDEX IDX_82D0213DBB649746 (updated_user_id), INDEX IDX_82D0213D97139001 (applicant_id), INDEX IDX_82D0213D6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_applicant_comments ADD CONSTRAINT FK_B075E80C19CF17B1 FOREIGN KEY (states_applicant_id) REFERENCES notes_states_applicants (id)");
        $this->addSql("ALTER TABLE notes_applicant_comments ADD CONSTRAINT FK_B075E80CBF396750 FOREIGN KEY (id) REFERENCES notes_comments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE notes_states_applicants ADD CONSTRAINT FK_82D0213DE104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_states_applicants ADD CONSTRAINT FK_82D0213DBB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_states_applicants ADD CONSTRAINT FK_82D0213D97139001 FOREIGN KEY (applicant_id) REFERENCES notes_applicants (id)");
        $this->addSql("ALTER TABLE notes_states_applicants ADD CONSTRAINT FK_82D0213D6BF700BD FOREIGN KEY (status_id) REFERENCES notes_status (id)");
        $this->addSql("ALTER TABLE notes_notifications ADD applicant_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_notifications ADD CONSTRAINT FK_6923A93F97139001 FOREIGN KEY (applicant_id) REFERENCES notes_applicants (id)");
        $this->addSql("CREATE INDEX IDX_6923A93F97139001 ON notes_notifications (applicant_id)");

        // Status workflow data migration (scoped)
        $workflow = $this->connection->fetchAll("SELECT parent_id, status_id FROM notes_status_workflow WHERE discr = 'default'");
        $workflowValues = array();
        foreach (array('travelExpense', 'leave') as $discr) {
            foreach ($workflow as $values) {
                $workflowValues[] = "(". ($values['parent_id'] ? $values['parent_id'] : 'NULL') . ", " . $values['status_id'] . ", '" . $discr . "')";
            }
        }

        // Truncate table data, status does not require an auto increment reset.
        // Datafixtures set explicit ids
        $this->addSql("DELETE FROM notes_status_workflow");
        $this->addSql("ALTER TABLE notes_status_workflow AUTO_INCREMENT = 1");
        $this->addSql("INSERT INTO notes_status_workflow (parent_id, status_id, discr) VALUES " . implode(',', $workflowValues));

        // Insert status and status workflow
        $this->addSql("INSERT INTO notes_status (id, name) VALUES (13, 'Hired'), (12, 'Interview failed'), (11, 'Interview passed'), (10, 'Scheduled interview'), (7, 'Scheduled written exam'), (9, 'Written exam failed'), (8, 'Written exam passed')");
        $this->addSql("INSERT INTO notes_status_workflow (id, parent_id, status_id, discr) VALUES (15, NULL, 1, 'applicant'), (16, 1, 7, 'applicant'), (17, 11, 7, 'applicant'), (18, 8, 7, 'applicant'), (19, 7, 8, 'applicant'), (20, 7, 9, 'applicant'), (21, 1, 10, 'applicant'), (22, 11, 10, 'applicant'), (23, 8, 10, 'applicant'), (24, 10, 11, 'applicant'), (25, 10, 12, 'applicant'), (26, 1, 5, 'applicant'), (27, 10, 5, 'applicant'), (28, 11, 5, 'applicant'), (29, 12, 5, 'applicant'), (30, 7, 5, 'applicant'), (31, 8, 5, 'applicant'), (32, 9, 5, 'applicant'), (33, 8, 13, 'applicant'), (34, 11, 13, 'applicant')");
        $this->addSql("INSERT INTO notes_status_workflow (id, parent_id, status_id, discr) VALUES (35, NULL, 1, 'travelRequest'), (36, 1, 2, 'travelRequest'), (37, 2, 3, 'travelRequest'), (38, 2, 4, 'travelRequest'), (39, 3, 2, 'travelRequest'), (40, 2, 5, 'travelRequest')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE notes_applicant_comments DROP FOREIGN KEY FK_B075E80C19CF17B1");
        $this->addSql("DROP TABLE notes_applicant_comments");
        $this->addSql("DROP TABLE notes_applicant_tokens");
        $this->addSql("DROP TABLE notes_states_applicants");
        $this->addSql("ALTER TABLE notes_notifications DROP FOREIGN KEY FK_6923A93F97139001");
        $this->addSql("DROP INDEX IDX_6923A93F97139001 ON notes_notifications");
        $this->addSql("ALTER TABLE notes_notifications DROP applicant_id");

        // Revert workflow data migration
        $this->addSql("UPDATE notes_status_workflow SET discr = 'default' WHERE discr = 'travelExpense'");
        $this->addSql("DELETE FROM notes_status_workflow WHERE discr <> 'default'");
        // Delete hiring states
        $this->addSql("DELETE FROM notes_status WHERE id > 6");
    }
}
