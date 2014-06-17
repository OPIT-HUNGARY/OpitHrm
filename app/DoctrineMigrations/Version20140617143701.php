<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140617143701 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_job_position (id INT AUTO_INCREMENT NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, system TINYINT(1) DEFAULT '0' NOT NULL, number_of_positions INT NOT NULL, description LONGTEXT NOT NULL, is_active TINYINT(1) NOT NULL, hiringManager_id INT DEFAULT NULL, INDEX IDX_B43E8682E104C1D3 (created_user_id), INDEX IDX_B43E8682BB649746 (updated_user_id), INDEX IDX_B43E86827CA7CF27 (hiringManager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_job_position ADD CONSTRAINT FK_B43E8682E104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_job_position ADD CONSTRAINT FK_B43E8682BB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_job_position ADD CONSTRAINT FK_B43E86827CA7CF27 FOREIGN KEY (hiringManager_id) REFERENCES notes_users (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("DROP TABLE notes_job_position");
    }
}
