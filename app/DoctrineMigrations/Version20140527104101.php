<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140527104101 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE notes_leave_duration (id INT NOT NULL, leave_duration_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_leave_categories ADD leaveDuration_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_leave_categories ADD CONSTRAINT FK_13F3CE3762F1818F FOREIGN KEY (leaveDuration_id) REFERENCES notes_leave_duration (id)");
        $this->addSql("CREATE INDEX IDX_13F3CE3762F1818F ON notes_leave_categories (leaveDuration_id)");

        // Insert leave duration
        $this->addSql("INSERT INTO notes_leave_duration (id, leave_duration_name) VALUES (1,'Full day working hours'),(2,'Half day working hours')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE notes_leave_categories DROP FOREIGN KEY FK_13F3CE3762F1818F");
        $this->addSql("DROP TABLE notes_leave_duration");
        $this->addSql("DROP INDEX IDX_13F3CE3762F1818F ON notes_leave_categories");
        $this->addSql("ALTER TABLE notes_leave_categories DROP leaveDuration_id");
    }
}
