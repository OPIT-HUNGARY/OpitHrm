<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140513102128 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_leaves DROP FOREIGN KEY FK_4B0AF95F12469DE2");
        $this->addSql("ALTER TABLE notes_holiday_dates DROP FOREIGN KEY FK_8BEDEF35F791C99D");
        $this->addSql("CREATE TABLE notes_leave_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_leave_dates (id INT AUTO_INCREMENT NOT NULL, leave_type_id INT DEFAULT NULL, leaveDate DATE NOT NULL, INDEX IDX_842D1E7C8313F474 (leave_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_leave_categories (id INT AUTO_INCREMENT NOT NULL, deletedAt DATETIME DEFAULT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_leave_dates ADD CONSTRAINT FK_842D1E7C8313F474 FOREIGN KEY (leave_type_id) REFERENCES notes_leave_types (id)");
        $this->addSql("DROP TABLE notes_holiday_categories");
        $this->addSql("DROP TABLE notes_holiday_dates");
        $this->addSql("DROP TABLE notes_holiday_types");
        $this->addSql("ALTER TABLE notes_leave_settings DROP FOREIGN KEY FK_72D8052C19DE2905");
        $this->addSql("DROP INDEX IDX_72D8052C19DE2905 ON notes_leave_settings");
        $this->addSql("ALTER TABLE notes_leave_settings CHANGE holiday_group_id leave_group_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_leave_settings ADD CONSTRAINT FK_72D8052CC07CF1E4 FOREIGN KEY (leave_group_id) REFERENCES notes_leave_groups (id)");
        $this->addSql("CREATE INDEX IDX_72D8052CC07CF1E4 ON notes_leave_settings (leave_group_id)");
        $this->addSql("ALTER TABLE notes_leaves ADD CONSTRAINT FK_4B0AF95F12469DE2 FOREIGN KEY (category_id) REFERENCES notes_leave_categories (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_leave_dates DROP FOREIGN KEY FK_842D1E7C8313F474");
        $this->addSql("ALTER TABLE notes_leaves DROP FOREIGN KEY FK_4B0AF95F12469DE2");
        $this->addSql("CREATE TABLE notes_holiday_categories (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, deletedAt DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_holiday_dates (id INT AUTO_INCREMENT NOT NULL, holiday_type_id INT DEFAULT NULL, holidayDate DATE NOT NULL, INDEX IDX_8BEDEF35F791C99D (holiday_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_holiday_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_holiday_dates ADD CONSTRAINT FK_8BEDEF35F791C99D FOREIGN KEY (holiday_type_id) REFERENCES notes_holiday_types (id)");
        $this->addSql("DROP TABLE notes_leave_types");
        $this->addSql("DROP TABLE notes_leave_dates");
        $this->addSql("DROP TABLE notes_leave_categories");
        $this->addSql("ALTER TABLE notes_leave_settings DROP FOREIGN KEY FK_72D8052CC07CF1E4");
        $this->addSql("DROP INDEX IDX_72D8052CC07CF1E4 ON notes_leave_settings");
        $this->addSql("ALTER TABLE notes_leave_settings CHANGE leave_group_id holiday_group_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_leave_settings ADD CONSTRAINT FK_72D8052C19DE2905 FOREIGN KEY (holiday_group_id) REFERENCES notes_leave_groups (id)");
        $this->addSql("CREATE INDEX IDX_72D8052C19DE2905 ON notes_leave_settings (holiday_group_id)");
        $this->addSql("ALTER TABLE notes_leaves ADD CONSTRAINT FK_4B0AF95F12469DE2 FOREIGN KEY (category_id) REFERENCES notes_holiday_categories (id)");
    }
}
