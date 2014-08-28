<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140828153122 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_job_position ADD location_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_job_position ADD CONSTRAINT FK_871F60A464D218E FOREIGN KEY (location_id) REFERENCES opithrm_common_types (id)");
        $this->addSql("CREATE INDEX IDX_871F60A464D218E ON opithrm_job_position (location_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_job_position DROP FOREIGN KEY FK_871F60A464D218E");
        $this->addSql("DROP INDEX IDX_871F60A464D218E ON opithrm_job_position");
        $this->addSql("ALTER TABLE opithrm_job_position DROP location_id");
    }
}
