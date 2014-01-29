<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140129160743 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_states_travel_expense ADD created_user_id INT DEFAULT NULL, ADD updated_user_id INT DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL");
        $this->addSql("ALTER TABLE notes_states_travel_expense ADD CONSTRAINT FK_D171F08AE104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_states_travel_expense ADD CONSTRAINT FK_D171F08ABB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("CREATE INDEX IDX_D171F08AE104C1D3 ON notes_states_travel_expense (created_user_id)");
        $this->addSql("CREATE INDEX IDX_D171F08ABB649746 ON notes_states_travel_expense (updated_user_id)");
        $this->addSql("ALTER TABLE notes_states_travel_requests ADD created_user_id INT DEFAULT NULL, ADD updated_user_id INT DEFAULT NULL, ADD created DATETIME NOT NULL, ADD updated DATETIME NOT NULL");
        $this->addSql("ALTER TABLE notes_states_travel_requests ADD CONSTRAINT FK_49A1F1CFE104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_states_travel_requests ADD CONSTRAINT FK_49A1F1CFBB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("CREATE INDEX IDX_49A1F1CFE104C1D3 ON notes_states_travel_requests (created_user_id)");
        $this->addSql("CREATE INDEX IDX_49A1F1CFBB649746 ON notes_states_travel_requests (updated_user_id)");
        
        // Safety updates because of https://github.com/fabpot/Twig/pull/1311
        $this->addSql("UPDATE notes_states_travel_expense SET created='1970-01-01', updated='1970-01-01'");
        $this->addSql("UPDATE notes_states_travel_requests SET created='1970-01-01', updated='1970-01-01'");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_states_travel_expense DROP FOREIGN KEY FK_D171F08AE104C1D3");
        $this->addSql("ALTER TABLE notes_states_travel_expense DROP FOREIGN KEY FK_D171F08ABB649746");
        $this->addSql("DROP INDEX IDX_D171F08AE104C1D3 ON notes_states_travel_expense");
        $this->addSql("DROP INDEX IDX_D171F08ABB649746 ON notes_states_travel_expense");
        $this->addSql("ALTER TABLE notes_states_travel_expense DROP created_user_id, DROP updated_user_id, DROP created, DROP updated");
        $this->addSql("ALTER TABLE notes_states_travel_requests DROP FOREIGN KEY FK_49A1F1CFE104C1D3");
        $this->addSql("ALTER TABLE notes_states_travel_requests DROP FOREIGN KEY FK_49A1F1CFBB649746");
        $this->addSql("DROP INDEX IDX_49A1F1CFE104C1D3 ON notes_states_travel_requests");
        $this->addSql("DROP INDEX IDX_49A1F1CFBB649746 ON notes_states_travel_requests");
        $this->addSql("ALTER TABLE notes_states_travel_requests DROP created_user_id, DROP updated_user_id, DROP created, DROP updated");
    }
}
