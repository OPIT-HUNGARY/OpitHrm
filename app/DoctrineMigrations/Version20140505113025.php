<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140505113025 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE Employee (id INT AUTO_INCREMENT NOT NULL, dateOfBirth DATE NOT NULL, joiningDate DATE NOT NULL, numberOfKids SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_users ADD employee INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_users ADD CONSTRAINT FK_8E744D495D9F75A1 FOREIGN KEY (employee) REFERENCES Employee (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_8E744D495D9F75A1 ON notes_users (employee)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_users DROP FOREIGN KEY FK_8E744D495D9F75A1");
        $this->addSql("DROP TABLE Employee");
        $this->addSql("DROP INDEX UNIQ_8E744D495D9F75A1 ON notes_users");
        $this->addSql("ALTER TABLE notes_users DROP employee");
    }
}
