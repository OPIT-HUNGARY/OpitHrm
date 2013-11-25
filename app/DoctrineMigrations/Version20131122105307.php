<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131122105307 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_users_groups (user_id INT NOT NULL, groups_id INT NOT NULL, INDEX IDX_6ADF70ECA76ED395 (user_id), INDEX IDX_6ADF70ECF373DCF (groups_id), PRIMARY KEY(user_id, groups_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, role VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_26216D7057698A6A (role), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_users_groups ADD CONSTRAINT FK_6ADF70ECA76ED395 FOREIGN KEY (user_id) REFERENCES notes_users (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE notes_users_groups ADD CONSTRAINT FK_6ADF70ECF373DCF FOREIGN KEY (groups_id) REFERENCES notes_groups (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_users_groups DROP FOREIGN KEY FK_6ADF70ECF373DCF");
        $this->addSql("DROP TABLE notes_users_groups");
        $this->addSql("DROP TABLE notes_groups");
    }
}
