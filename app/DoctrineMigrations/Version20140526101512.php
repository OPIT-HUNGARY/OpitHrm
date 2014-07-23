<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140526101512 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_travel_tokens (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, travel_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_states_leave_request (id INT AUTO_INCREMENT NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, leave_request_id INT DEFAULT NULL, status_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_AD971D1DE104C1D3 (created_user_id), INDEX IDX_AD971D1DBB649746 (updated_user_id), INDEX IDX_AD971D1DF2E1C15D (leave_request_id), INDEX IDX_AD971D1D6BF700BD (status_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_leave_tokens (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, leave_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_states_leave_request ADD CONSTRAINT FK_AD971D1DE104C1D3 FOREIGN KEY (created_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_states_leave_request ADD CONSTRAINT FK_AD971D1DBB649746 FOREIGN KEY (updated_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_states_leave_request ADD CONSTRAINT FK_AD971D1DF2E1C15D FOREIGN KEY (leave_request_id) REFERENCES opithrm_leave_request (id)");
        $this->addSql("ALTER TABLE opithrm_states_leave_request ADD CONSTRAINT FK_AD971D1D6BF700BD FOREIGN KEY (status_id) REFERENCES opithrm_status (id)");
        $this->addSql("DROP TABLE opithrm_tokens");
        $this->addSql("ALTER TABLE opithrm_leave_request ADD generalManager_id INT DEFAULT NULL, ADD teamManager_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_leave_request ADD CONSTRAINT FK_74EBEE9499CB8429 FOREIGN KEY (generalManager_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_leave_request ADD CONSTRAINT FK_74EBEE94CDFA8849 FOREIGN KEY (teamManager_id) REFERENCES opithrm_users (id)");
        $this->addSql("CREATE INDEX IDX_74EBEE9499CB8429 ON opithrm_leave_request (generalManager_id)");
        $this->addSql("CREATE INDEX IDX_74EBEE94CDFA8849 ON opithrm_leave_request (teamManager_id)");
        $this->addSql("ALTER TABLE opithrm_notifications ADD lr_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE opithrm_notifications ADD CONSTRAINT FK_6923A93FC2D61964 FOREIGN KEY (lr_id) REFERENCES opithrm_leave_request (id)");
        $this->addSql("CREATE INDEX IDX_6923A93FC2D61964 ON opithrm_notifications (lr_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE opithrm_tokens (id INT AUTO_INCREMENT NOT NULL, token VARCHAR(255) NOT NULL, travel_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("DROP TABLE opithrm_travel_tokens");
        $this->addSql("DROP TABLE opithrm_states_leave_request");
        $this->addSql("DROP TABLE opithrm_leave_tokens");
        $this->addSql("ALTER TABLE opithrm_leave_request DROP FOREIGN KEY FK_74EBEE9499CB8429");
        $this->addSql("ALTER TABLE opithrm_leave_request DROP FOREIGN KEY FK_74EBEE94CDFA8849");
        $this->addSql("DROP INDEX IDX_74EBEE9499CB8429 ON opithrm_leave_request");
        $this->addSql("DROP INDEX IDX_74EBEE94CDFA8849 ON opithrm_leave_request");
        $this->addSql("ALTER TABLE opithrm_leave_request DROP generalManager_id, DROP teamManager_id");
        $this->addSql("ALTER TABLE opithrm_notifications DROP FOREIGN KEY FK_6923A93FC2D61964");
        $this->addSql("DROP INDEX IDX_6923A93FC2D61964 ON opithrm_notifications");
        $this->addSql("ALTER TABLE opithrm_notifications DROP lr_id");
    }
}
