<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131128144459 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_tr_destination (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, cost INT NOT NULL, travelRequest_id INT DEFAULT NULL, transportationType_id INT DEFAULT NULL, INDEX IDX_4ACB4B274663DE14 (travelRequest_id), UNIQUE INDEX UNIQ_4ACB4B27B2DE2FB7 (transportationType_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_travel_request (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, departure_date DATE NOT NULL, arrival_date DATE NOT NULL, trip_purpose VARCHAR(255) NOT NULL, customer_related TINYINT(1) NOT NULL, opportunity_name VARCHAR(255) NOT NULL, teamManager_id INT DEFAULT NULL, generalManager_id INT DEFAULT NULL, INDEX IDX_5361B5C4A76ED395 (user_id), INDEX IDX_5361B5C4CDFA8849 (teamManager_id), INDEX IDX_5361B5C499CB8429 (generalManager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_transportation_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_tr_accomodation (id INT AUTO_INCREMENT NOT NULL, number_of_nights INT NOT NULL, cost INT NOT NULL, city VARCHAR(255) NOT NULL, hotel_name VARCHAR(255) NOT NULL, travelRequest_id INT DEFAULT NULL, INDEX IDX_C170735B4663DE14 (travelRequest_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_tr_destination ADD CONSTRAINT FK_4ACB4B274663DE14 FOREIGN KEY (travelRequest_id) REFERENCES notes_travel_request (id)");
        $this->addSql("ALTER TABLE notes_tr_destination ADD CONSTRAINT FK_4ACB4B27B2DE2FB7 FOREIGN KEY (transportationType_id) REFERENCES notes_transportation_type (id)");
        $this->addSql("ALTER TABLE notes_travel_request ADD CONSTRAINT FK_5361B5C4A76ED395 FOREIGN KEY (user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_travel_request ADD CONSTRAINT FK_5361B5C4CDFA8849 FOREIGN KEY (teamManager_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_travel_request ADD CONSTRAINT FK_5361B5C499CB8429 FOREIGN KEY (generalManager_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_tr_accomodation ADD CONSTRAINT FK_C170735B4663DE14 FOREIGN KEY (travelRequest_id) REFERENCES notes_travel_request (id)");
        $this->addSql("ALTER TABLE notes_users ADD discr VARCHAR(255) NOT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_tr_destination DROP FOREIGN KEY FK_4ACB4B274663DE14");
        $this->addSql("ALTER TABLE notes_tr_accomodation DROP FOREIGN KEY FK_C170735B4663DE14");
        $this->addSql("ALTER TABLE notes_tr_destination DROP FOREIGN KEY FK_4ACB4B27B2DE2FB7");
        $this->addSql("DROP TABLE notes_tr_destination");
        $this->addSql("DROP TABLE notes_travel_request");
        $this->addSql("DROP TABLE notes_transportation_type");
        $this->addSql("DROP TABLE notes_tr_accomodation");
        $this->addSql("ALTER TABLE notes_users DROP discr");
    }
}
