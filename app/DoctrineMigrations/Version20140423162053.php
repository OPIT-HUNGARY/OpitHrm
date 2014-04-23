<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140423162053 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        if ($schema->getTable('notes_users')->hasIndex('UNIQ_8E744D49F85E0677')) {
            $this->addSql("DROP INDEX UNIQ_8E744D49F85E0677 ON notes_users");
        }
        if ($schema->getTable('notes_users')->hasIndex('UNIQ_8E744D49E7927C74')) {
            $this->addSql("DROP INDEX UNIQ_8E744D49E7927C74 ON notes_users");
        }
        if ($schema->getTable('notes_users')->hasIndex('UNIQ_8E744D49BFA0107D')) {
            $this->addSql("DROP INDEX UNIQ_8E744D49BFA0107D ON notes_users");
        }
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
    }
}
