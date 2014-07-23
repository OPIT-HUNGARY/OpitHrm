<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140505133713 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_users DROP FOREIGN KEY FK_8E744D495D9F75A1");
        $this->addSql("CREATE TABLE opithrm_teams (id INT AUTO_INCREMENT NOT NULL, teamName VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_employees (id INT AUTO_INCREMENT NOT NULL, dateOfBirth DATE NOT NULL, joiningDate DATE NOT NULL, numberOfKids SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE opithrm_employees_teams (employee_id INT NOT NULL, teams_id INT NOT NULL, INDEX IDX_AB7644BA8C03F15C (employee_id), INDEX IDX_AB7644BAD6365F12 (teams_id), PRIMARY KEY(employee_id, teams_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE opithrm_employees_teams ADD CONSTRAINT FK_AB7644BA8C03F15C FOREIGN KEY (employee_id) REFERENCES opithrm_employees (id) ON DELETE CASCADE");
        $this->addSql("ALTER TABLE opithrm_employees_teams ADD CONSTRAINT FK_AB7644BAD6365F12 FOREIGN KEY (teams_id) REFERENCES opithrm_teams (id) ON DELETE CASCADE");

        // Insert employees
        $users = $this->connection->fetchAll("SELECT u.id, e.dateOfBirth, e.joiningDate, e.numberOfKids FROM opithrm_users u LEFT JOIN Employee e ON u.employee = e.id");

        $inserts = array();
        foreach ($users as $user) {
            $inserts[] = "(" . $user['id'] . ", '" . $user['dateOfBirth'] . "', '" . $user['joiningDate'] . "', " . (!empty($user['numberOfKids']) ? $user['numberOfKids'] : 0) . ")";
        }
        $this->addSql("INSERT INTO opithrm_employees (id, dateOfBirth, joiningDate, numberOfKids) VALUES " . implode(', ', $inserts));
        // Update user to employee relationship
        $this->addSql("UPDATE opithrm_users SET employee =  id");

        $this->addSql("DROP TABLE Employee");
        $this->addSql("ALTER TABLE opithrm_users ADD CONSTRAINT FK_8E744D495D9F75A1 FOREIGN KEY (employee) REFERENCES opithrm_employees (id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_employees_teams DROP FOREIGN KEY FK_AB7644BAD6365F12");
        $this->addSql("ALTER TABLE opithrm_employees_teams DROP FOREIGN KEY FK_AB7644BA8C03F15C");
        $this->addSql("ALTER TABLE opithrm_users DROP FOREIGN KEY FK_8E744D495D9F75A1");
        $this->addSql("CREATE TABLE Employee (id INT AUTO_INCREMENT NOT NULL, dateOfBirth DATE NOT NULL, joiningDate DATE NOT NULL, numberOfKids SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");

        // Insert employees to old table
        $this->addSql("INSERT INTO Employee (id, dateOfBirth, joiningDate, numberOfKids) SELECT id, dateOfBirth, joiningDate, numberOfKids FROM opithrm_employees");

        $this->addSql("DROP TABLE opithrm_teams");
        $this->addSql("DROP TABLE opithrm_employees");
        $this->addSql("DROP TABLE opithrm_employees_teams");
        $this->addSql("ALTER TABLE opithrm_users ADD CONSTRAINT FK_8E744D495D9F75A1 FOREIGN KEY (employee) REFERENCES Employee (id)");
    }
}
