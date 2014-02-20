<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140220105605 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_te_advances_received (id INT AUTO_INCREMENT NOT NULL, travel_expense INT DEFAULT NULL, currency VARCHAR(3) DEFAULT NULL, advances_received DOUBLE PRECISION NOT NULL, INDEX IDX_18DB08A6EC793AB7 (travel_expense), INDEX IDX_18DB08A66956883F (currency), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_te_advances_received ADD CONSTRAINT FK_18DB08A6EC793AB7 FOREIGN KEY (travel_expense) REFERENCES notes_travel_expense (id)");
        $this->addSql("ALTER TABLE notes_te_advances_received ADD CONSTRAINT FK_18DB08A66956883F FOREIGN KEY (currency) REFERENCES notes_currencies (code)");
        $this->addSql("ALTER TABLE notes_travel_expense DROP FOREIGN KEY FK_45CCB7FD38248176");
        $this->addSql("DROP INDEX IDX_45CCB7FD38248176 ON notes_travel_expense");
        $this->addSql("ALTER TABLE notes_travel_expense DROP currency_id, DROP advances_payback, DROP to_settle, DROP advances_recieved");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE notes_te_advances_received");
        $this->addSql("ALTER TABLE notes_travel_expense ADD currency_id VARCHAR(3) DEFAULT NULL, ADD advances_payback DOUBLE PRECISION NOT NULL, ADD to_settle DOUBLE PRECISION NOT NULL, ADD advances_recieved DOUBLE PRECISION NOT NULL");
        $this->addSql("ALTER TABLE notes_travel_expense ADD CONSTRAINT FK_45CCB7FD38248176 FOREIGN KEY (currency_id) REFERENCES notes_currencies (code)");
        $this->addSql("CREATE INDEX IDX_45CCB7FD38248176 ON notes_travel_expense (currency_id)");
    }
}
