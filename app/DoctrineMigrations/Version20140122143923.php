<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140122143923 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_travel_expense ADD currency_id VARCHAR(3) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_travel_expense ADD CONSTRAINT FK_45CCB7FD38248176 FOREIGN KEY (currency_id) REFERENCES notes_currencies (code)");
        $this->addSql("CREATE INDEX IDX_45CCB7FD38248176 ON notes_travel_expense (currency_id)");
        $this->addSql("ALTER TABLE notes_tr_destination ADD currency_id VARCHAR(3) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_tr_destination ADD CONSTRAINT FK_4ACB4B2738248176 FOREIGN KEY (currency_id) REFERENCES notes_currencies (code)");
        $this->addSql("CREATE INDEX IDX_4ACB4B2738248176 ON notes_tr_destination (currency_id)");
        $this->addSql("ALTER TABLE notes_te_paid_expense ADD currency_id VARCHAR(3) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_te_paid_expense ADD CONSTRAINT FK_4EEA268738248176 FOREIGN KEY (currency_id) REFERENCES notes_currencies (code)");
        $this->addSql("CREATE INDEX IDX_4EEA268738248176 ON notes_te_paid_expense (currency_id)");
        $this->addSql("ALTER TABLE notes_tr_accomodation ADD currency_id VARCHAR(3) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_tr_accomodation ADD CONSTRAINT FK_C170735B38248176 FOREIGN KEY (currency_id) REFERENCES notes_currencies (code)");
        $this->addSql("CREATE INDEX IDX_C170735B38248176 ON notes_tr_accomodation (currency_id)");
        $this->addSql("ALTER TABLE notes_te_per_diem ADD currency_id VARCHAR(3) DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_te_per_diem ADD CONSTRAINT FK_4684B3838248176 FOREIGN KEY (currency_id) REFERENCES notes_currencies (code)");
        $this->addSql("CREATE INDEX IDX_4684B3838248176 ON notes_te_per_diem (currency_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_te_paid_expense DROP FOREIGN KEY FK_4EEA268738248176");
        $this->addSql("DROP INDEX IDX_4EEA268738248176 ON notes_te_paid_expense");
        $this->addSql("ALTER TABLE notes_te_paid_expense DROP currency_id");
        $this->addSql("ALTER TABLE notes_te_per_diem DROP FOREIGN KEY FK_4684B3838248176");
        $this->addSql("DROP INDEX IDX_4684B3838248176 ON notes_te_per_diem");
        $this->addSql("ALTER TABLE notes_te_per_diem DROP currency_id");
        $this->addSql("ALTER TABLE notes_tr_accomodation DROP FOREIGN KEY FK_C170735B38248176");
        $this->addSql("DROP INDEX IDX_C170735B38248176 ON notes_tr_accomodation");
        $this->addSql("ALTER TABLE notes_tr_accomodation DROP currency_id");
        $this->addSql("ALTER TABLE notes_tr_destination DROP FOREIGN KEY FK_4ACB4B2738248176");
        $this->addSql("DROP INDEX IDX_4ACB4B2738248176 ON notes_tr_destination");
        $this->addSql("ALTER TABLE notes_tr_destination DROP currency_id");
        $this->addSql("ALTER TABLE notes_travel_expense DROP FOREIGN KEY FK_45CCB7FD38248176");
        $this->addSql("DROP INDEX IDX_45CCB7FD38248176 ON notes_travel_expense");
        $this->addSql("ALTER TABLE notes_travel_expense DROP currency_id");
    }
}
