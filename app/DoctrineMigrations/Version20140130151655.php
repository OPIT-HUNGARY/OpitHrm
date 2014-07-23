<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140130151655 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_notifications ADD read_id INT DEFAULT NULL, DROP notification_read");
        $this->addSql("ALTER TABLE opithrm_notifications ADD CONSTRAINT FK_6923A93F37B4A21E FOREIGN KEY (read_id) REFERENCES opithrm_notification_status (id)");
        $this->addSql("CREATE INDEX IDX_6923A93F37B4A21E ON opithrm_notifications (read_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_notifications DROP FOREIGN KEY FK_6923A93F37B4A21E");
        $this->addSql("DROP INDEX IDX_6923A93F37B4A21E ON opithrm_notifications");
        $this->addSql("ALTER TABLE opithrm_notifications ADD notification_read INT NOT NULL, DROP read_id");
    }
}
