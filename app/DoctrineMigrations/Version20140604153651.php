<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140604153651 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_leave_types ADD is_working_day TINYINT(1) NOT NULL");
        $this->addSql("ALTER TABLE notes_leave_request ADD created_user_id INT DEFAULT NULL, ADD updated_user_id INT DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, ADD system TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("ALTER TABLE notes_leave_request ADD CONSTRAINT FK_74EBEE94E104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_leave_request ADD CONSTRAINT FK_74EBEE94BB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("CREATE INDEX IDX_74EBEE94E104C1D3 ON notes_leave_request (created_user_id)");
        $this->addSql("CREATE INDEX IDX_74EBEE94BB649746 ON notes_leave_request (updated_user_id)");
        $this->addSql("ALTER TABLE notes_leave_categories ADD is_paid TINYINT(1) NOT NULL, ADD is_counted_as_leave TINYINT(1) NOT NULL");
        $this->addSql("ALTER TABLE notes_leaves ADD number_of_days INT NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_leave_categories DROP is_paid, DROP is_counted_as_leave");
        $this->addSql("ALTER TABLE notes_leave_request DROP FOREIGN KEY FK_74EBEE94E104C1D3");
        $this->addSql("ALTER TABLE notes_leave_request DROP FOREIGN KEY FK_74EBEE94BB649746");
        $this->addSql("DROP INDEX IDX_74EBEE94E104C1D3 ON notes_leave_request");
        $this->addSql("DROP INDEX IDX_74EBEE94BB649746 ON notes_leave_request");
        $this->addSql("ALTER TABLE notes_leave_request DROP created_user_id, DROP updated_user_id, DROP created, DROP updated, DROP system");
        $this->addSql("ALTER TABLE notes_leave_types DROP is_working_day");
        $this->addSql("ALTER TABLE notes_leaves DROP number_of_days");
    }
}
