<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140128115207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_notifications (id INT AUTO_INCREMENT NOT NULL, reciever_id INT DEFAULT NULL, te_id INT DEFAULT NULL, tr_id INT DEFAULT NULL, message VARCHAR(255) NOT NULL, date_time DATETIME NOT NULL, notification_read INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_6923A93F5D5C928D (reciever_id), INDEX IDX_6923A93F5F88AA01 (te_id), INDEX IDX_6923A93F9246C527 (tr_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_notification_status (id INT AUTO_INCREMENT NOT NULL, notification_status_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_notifications ADD CONSTRAINT FK_6923A93F5D5C928D FOREIGN KEY (reciever_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_notifications ADD CONSTRAINT FK_6923A93F5F88AA01 FOREIGN KEY (te_id) REFERENCES notes_travel_expense (id)");
        $this->addSql("ALTER TABLE notes_notifications ADD CONSTRAINT FK_6923A93F9246C527 FOREIGN KEY (tr_id) REFERENCES notes_travel_request (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE notes_notifications");
        $this->addSql("DROP TABLE notes_notification_status");
    }
}
