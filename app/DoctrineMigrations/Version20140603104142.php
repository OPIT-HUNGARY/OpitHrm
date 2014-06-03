<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140603104142 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_states_travel_expense ADD system TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("ALTER TABLE notes_states_travel_requests ADD system TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("ALTER TABLE notes_rates ADD system TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("ALTER TABLE notes_states_leave_request ADD system TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("ALTER TABLE notes_leave_categories ADD created_user_id INT DEFAULT NULL, ADD updated_user_id INT DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL, ADD system TINYINT(1) DEFAULT '0' NOT NULL");
        $this->addSql("ALTER TABLE notes_leave_categories ADD CONSTRAINT FK_13F3CE37E104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_leave_categories ADD CONSTRAINT FK_13F3CE37BB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("CREATE INDEX IDX_13F3CE37E104C1D3 ON notes_leave_categories (created_user_id)");
        $this->addSql("CREATE INDEX IDX_13F3CE37BB649746 ON notes_leave_categories (updated_user_id)");
        $this->addSql("ALTER TABLE notes_log_timesheet ADD system TINYINT(1) DEFAULT '0' NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_leave_categories DROP FOREIGN KEY FK_13F3CE37E104C1D3");
        $this->addSql("ALTER TABLE notes_leave_categories DROP FOREIGN KEY FK_13F3CE37BB649746");
        $this->addSql("DROP INDEX IDX_13F3CE37E104C1D3 ON notes_leave_categories");
        $this->addSql("DROP INDEX IDX_13F3CE37BB649746 ON notes_leave_categories");
        $this->addSql("ALTER TABLE notes_leave_categories DROP created_user_id, DROP updated_user_id, DROP created, DROP updated, DROP system");
        $this->addSql("ALTER TABLE notes_log_timesheet DROP system");
        $this->addSql("ALTER TABLE notes_rates DROP system");
        $this->addSql("ALTER TABLE notes_states_leave_request DROP system");
        $this->addSql("ALTER TABLE notes_states_travel_expense DROP system");
        $this->addSql("ALTER TABLE notes_states_travel_requests DROP system");
    }
}
