<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140512120801 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE opithrm_leave_groups (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_leave_settings (id INT AUTO_INCREMENT NOT NULL, holiday_group_id INT DEFAULT NULL, number INT NOT NULL, number_of_leaves INT NOT NULL, INDEX IDX_72D8052C19DE2905 (holiday_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_leave_settings ADD CONSTRAINT FK_72D8052C19DE2905 FOREIGN KEY (holiday_group_id) REFERENCES opithrm_leave_groups (id)");

        // Insert entitlement groups
        $this->addSql("INSERT INTO opithrm_leave_groups(id,name) VALUES (1,'Age'),(2,'Children')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_leave_settings DROP FOREIGN KEY FK_72D8052C19DE2905");
        $this->addSql("DROP TABLE opithrm_leave_groups");
        $this->addSql("DROP TABLE opithrm_leave_settings");
    }
}
