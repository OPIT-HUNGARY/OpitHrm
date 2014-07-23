<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140506105857 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_employees ADD numberOfChildren INT NOT NULL, DROP numberOfKids");
        $this->addSql("ALTER TABLE opithrm_employees_teams DROP FOREIGN KEY FK_AB7644BAD6365F12");
        $this->addSql("DROP INDEX IDX_AB7644BAD6365F12 ON opithrm_employees_teams");
        $this->addSql("ALTER TABLE opithrm_employees_teams DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE opithrm_employees_teams CHANGE teams_id team_id INT NOT NULL");
        $this->addSql("ALTER TABLE opithrm_employees_teams ADD CONSTRAINT FK_AB7644BA296CD8AE FOREIGN KEY (team_id) REFERENCES opithrm_teams (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_AB7644BA296CD8AE ON opithrm_employees_teams (team_id)");
        $this->addSql("ALTER TABLE opithrm_employees_teams ADD PRIMARY KEY (employee_id, team_id)");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");
        
        $this->addSql("ALTER TABLE opithrm_employees ADD numberOfKids SMALLINT NOT NULL, DROP numberOfChildren");
        $this->addSql("ALTER TABLE opithrm_employees_teams DROP FOREIGN KEY FK_AB7644BA296CD8AE");
        $this->addSql("DROP INDEX IDX_AB7644BA296CD8AE ON opithrm_employees_teams");
        $this->addSql("ALTER TABLE opithrm_employees_teams DROP PRIMARY KEY");
        $this->addSql("ALTER TABLE opithrm_employees_teams CHANGE team_id teams_id INT NOT NULL");
        $this->addSql("ALTER TABLE opithrm_employees_teams ADD CONSTRAINT FK_AB7644BAD6365F12 FOREIGN KEY (teams_id) REFERENCES opithrm_teams (id) ON DELETE CASCADE");
        $this->addSql("CREATE INDEX IDX_AB7644BAD6365F12 ON opithrm_employees_teams (teams_id)");
        $this->addSql("ALTER TABLE opithrm_employees_teams ADD PRIMARY KEY (employee_id, teams_id)");
    }
}
