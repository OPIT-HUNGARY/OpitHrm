<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140131110235 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_notifications DROP FOREIGN KEY FK_6923A93F5D5C928D");
        $this->addSql("DROP INDEX IDX_6923A93F5D5C928D ON notes_notifications");
        $this->addSql("ALTER TABLE notes_notifications CHANGE reciever_id receiver_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_notifications ADD CONSTRAINT FK_6923A93FCD53EDB6 FOREIGN KEY (receiver_id) REFERENCES notes_users (id)");
        $this->addSql("CREATE INDEX IDX_6923A93FCD53EDB6 ON notes_notifications (receiver_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_notifications DROP FOREIGN KEY FK_6923A93FCD53EDB6");
        $this->addSql("DROP INDEX IDX_6923A93FCD53EDB6 ON notes_notifications");
        $this->addSql("ALTER TABLE notes_notifications CHANGE receiver_id reciever_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_notifications ADD CONSTRAINT FK_6923A93F5D5C928D FOREIGN KEY (reciever_id) REFERENCES notes_users (id)");
        $this->addSql("CREATE INDEX IDX_6923A93F5D5C928D ON notes_notifications (reciever_id)");
    }
}
