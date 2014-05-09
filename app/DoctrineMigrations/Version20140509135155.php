<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140509135155 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("CREATE TABLE notes_leave_request (id INT AUTO_INCREMENT NOT NULL, employee_id INT DEFAULT NULL, leave_request_id VARCHAR(11) DEFAULT NULL, INDEX IDX_74EBEE948C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_leaves (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, description LONGTEXT DEFAULT NULL, leaveRequest_id INT DEFAULT NULL, INDEX IDX_4B0AF95F12469DE2 (category_id), INDEX IDX_4B0AF95FF5EC012 (leaveRequest_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_leave_request ADD CONSTRAINT FK_74EBEE948C03F15C FOREIGN KEY (employee_id) REFERENCES notes_employees (id)");
        $this->addSql("ALTER TABLE notes_leaves ADD CONSTRAINT FK_4B0AF95F12469DE2 FOREIGN KEY (category_id) REFERENCES notes_holiday_categories (id)");
        $this->addSql("ALTER TABLE notes_leaves ADD CONSTRAINT FK_4B0AF95FF5EC012 FOREIGN KEY (leaveRequest_id) REFERENCES notes_leave_request (id)");
        $this->addSql("ALTER TABLE notes_employees ADD deletedAt DATETIME DEFAULT NULL, ADD date_of_birth DATE NOT NULL, ADD joining_date DATE NOT NULL, DROP dateOfBirth, DROP joiningDate, CHANGE numberofchildren number_of_children INT NOT NULL");
        $this->addSql("ALTER TABLE notes_users DROP FOREIGN KEY FK_8E744D495D9F75A1");
        $this->addSql("DROP INDEX UNIQ_8E744D495D9F75A1 ON notes_users");
        $this->addSql("ALTER TABLE notes_users CHANGE employee employee_id INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_users ADD CONSTRAINT FK_8E744D498C03F15C FOREIGN KEY (employee_id) REFERENCES notes_employees (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_8E744D498C03F15C ON notes_users (employee_id)");
        $this->addSql("ALTER TABLE notes_holiday_categories ADD deletedAt DATETIME DEFAULT NULL");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE notes_leaves DROP FOREIGN KEY FK_4B0AF95FF5EC012");
        $this->addSql("DROP TABLE notes_leave_request");
        $this->addSql("DROP TABLE notes_leaves");
        $this->addSql("ALTER TABLE notes_employees ADD dateOfBirth DATE NOT NULL, ADD joiningDate DATE NOT NULL, DROP deletedAt, DROP date_of_birth, DROP joining_date, CHANGE number_of_children numberOfChildren INT NOT NULL");
        $this->addSql("ALTER TABLE notes_holiday_categories DROP deletedAt");
        $this->addSql("ALTER TABLE notes_users DROP FOREIGN KEY FK_8E744D498C03F15C");
        $this->addSql("DROP INDEX UNIQ_8E744D498C03F15C ON notes_users");
        $this->addSql("ALTER TABLE notes_users CHANGE employee_id employee INT DEFAULT NULL");
        $this->addSql("ALTER TABLE notes_users ADD CONSTRAINT FK_8E744D495D9F75A1 FOREIGN KEY (employee) REFERENCES notes_employees (id)");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_8E744D495D9F75A1 ON notes_users (employee)");
    }
}
