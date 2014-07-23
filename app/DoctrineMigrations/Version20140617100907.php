<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140617100907 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE opithrm_comments (id INT AUTO_INCREMENT NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, system TINYINT(1) DEFAULT '0' NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_3398D58BE104C1D3 (created_user_id), INDEX IDX_3398D58BBB649746 (updated_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_tr_status_comments (id INT NOT NULL, states_tr_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_6D8B3EC719A7A5FC (states_tr_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_te_status_comments (id INT NOT NULL, states_te_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_5A48DBF1D469CADA (states_te_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_leave_status_comments (id INT NOT NULL, states_lr_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_14AC68D7493779BF (states_lr_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_comments ADD CONSTRAINT FK_3398D58BE104C1D3 FOREIGN KEY (created_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_comments ADD CONSTRAINT FK_3398D58BBB649746 FOREIGN KEY (updated_user_id) REFERENCES opithrm_users (id)");
        $this->addSql("ALTER TABLE opithrm_tr_status_comments ADD CONSTRAINT FK_6D8B3EC719A7A5FC FOREIGN KEY (states_tr_id) REFERENCES opithrm_states_travel_requests (id)");
        $this->addSql("ALTER TABLE opithrm_tr_status_comments ADD CONSTRAINT FK_6D8B3EC7BF396750 FOREIGN KEY (id) REFERENCES opithrm_comments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE opithrm_te_status_comments ADD CONSTRAINT FK_5A48DBF1D469CADA FOREIGN KEY (states_te_id) REFERENCES opithrm_states_travel_expense (id)");
        $this->addSql("ALTER TABLE opithrm_te_status_comments ADD CONSTRAINT FK_5A48DBF1BF396750 FOREIGN KEY (id) REFERENCES opithrm_comments (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE opithrm_leave_status_comments ADD CONSTRAINT FK_14AC68D7493779BF FOREIGN KEY (states_lr_id) REFERENCES opithrm_states_leave_request (id)");
        $this->addSql("ALTER TABLE opithrm_leave_status_comments ADD CONSTRAINT FK_14AC68D7BF396750 FOREIGN KEY (id) REFERENCES opithrm_comments (id) ON DELETE CASCADE");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_tr_status_comments DROP FOREIGN KEY FK_6D8B3EC7BF396750");
        $this->addSql("ALTER TABLE opithrm_te_status_comments DROP FOREIGN KEY FK_5A48DBF1BF396750");
        $this->addSql("ALTER TABLE opithrm_leave_status_comments DROP FOREIGN KEY FK_14AC68D7BF396750");
        $this->addSql("DROP TABLE opithrm_comments");
        $this->addSql("DROP TABLE opithrm_tr_status_comments");
        $this->addSql("DROP TABLE opithrm_te_status_comments");
        $this->addSql("DROP TABLE opithrm_leave_status_comments");
    }
}
