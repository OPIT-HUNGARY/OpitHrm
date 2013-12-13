<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131213133938 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_users ADD CONSTRAINT FK_8E744D496DD822C6 FOREIGN KEY (job_title_id) REFERENCES notes_job_titles (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_8E744D496DD822C6 ON notes_users (job_title_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_users DROP FOREIGN KEY FK_8E744D496DD822C6");
        $this->addSql("DROP INDEX UNIQ_8E744D496DD822C6 ON notes_users");
    }
}
