<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140605144902 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE opithrm_leave_request_groups (id INT AUTO_INCREMENT NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, system TINYINT(1) DEFAULT '0' NOT NULL, INDEX IDX_85CE372BE104C1D3 (created_user_id), INDEX IDX_85CE372BBB649746 (updated_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_leave_request_groups ADD CONSTRAINT FK_85CE372BE104C1D3 FOREIGN KEY (created_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_leave_request_groups ADD CONSTRAINT FK_85CE372BBB649746 FOREIGN KEY (updated_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_leave_request ADD leave_request_group_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_leave_request ADD CONSTRAINT FK_74EBEE94260F2F4A FOREIGN KEY (leave_request_group_id) REFERENCES opithrm_leave_request_groups (id)");
        $this->addSql("CREATE INDEX IDX_74EBEE94260F2F4A ON opithrm_leave_request (leave_request_group_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_leave_request DROP FOREIGN KEY FK_74EBEE94260F2F4A");
        $this->addSql("DROP TABLE opithrm_leave_request_groups");
        $this->addSql("DROP INDEX IDX_74EBEE94260F2F4A ON opithrm_leave_request");
        $this->addSql("ALTER TABLE opithrm_leave_request DROP leave_request_group_id");
    }
}
