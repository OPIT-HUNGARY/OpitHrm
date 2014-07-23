<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140618135648 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_notifications ADD jp_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_notifications ADD CONSTRAINT FK_6923A93FE79F244F FOREIGN KEY (jp_id) REFERENCES opithrm_job_position (id)");
        $this->addSql("CREATE INDEX IDX_6923A93FE79F244F ON opithrm_notifications (jp_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_notifications DROP FOREIGN KEY FK_6923A93FE79F244F");
        $this->addSql("DROP INDEX IDX_6923A93FE79F244F ON opithrm_notifications");
        $this->addSql("ALTER TABLE opithrm_notifications DROP jp_id");
    }
}
