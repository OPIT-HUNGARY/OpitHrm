<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140506151108 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_holiday_dates (id INT AUTO_INCREMENT NOT NULL, holiday_type_id INT DEFAULT NULL, holidayDate DATE NOT NULL, INDEX IDX_8BEDEF35F791C99D (holiday_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_holiday_types (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_holiday_dates ADD CONSTRAINT FK_8BEDEF35F791C99D FOREIGN KEY (holiday_type_id) REFERENCES notes_holiday_types (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_holiday_dates DROP FOREIGN KEY FK_8BEDEF35F791C99D");
        $this->addSql("DROP TABLE notes_holiday_dates");
        $this->addSql("DROP TABLE notes_holiday_types");
    }
}
