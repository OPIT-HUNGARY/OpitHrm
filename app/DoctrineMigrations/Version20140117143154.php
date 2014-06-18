<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140117143154 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("CREATE TABLE notes_rates (id INT AUTO_INCREMENT NOT NULL, currency_code VARCHAR(3) DEFAULT NULL, rate DOUBLE PRECISION NOT NULL, created_user_id INT DEFAULT NULL, updated_user_id INT DEFAULT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, INDEX IDX_DE23439CE104C1D3 (created_user_id), INDEX IDX_DE23439CBB649746 (updated_user_id), INDEX IDX_DE23439CFDA273EC (currency_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("CREATE TABLE notes_currencies (code VARCHAR(3) NOT NULL, description VARCHAR(100) NOT NULL, PRIMARY KEY(code)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB");
        $this->addSql("ALTER TABLE notes_rates ADD CONSTRAINT FK_DE23439CE104C1D3 FOREIGN KEY (created_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_rates ADD CONSTRAINT FK_DE23439CBB649746 FOREIGN KEY (updated_user_id) REFERENCES notes_users (id)");
        $this->addSql("ALTER TABLE notes_rates ADD CONSTRAINT FK_DE23439CFDA273EC FOREIGN KEY (currency_code) REFERENCES notes_currencies (code)");

        // Insert some currencies
        $this->addSql("INSERT INTO notes_currencies (code, description) VALUES ('CHF','Swiss Franc'),('EUR','Euro'),('GBP','Pound Sterling'),('HUF','Hungarian Forint'),('USD','US Dollar')");
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE notes_rates DROP FOREIGN KEY FK_DE23439CFDA273EC");
        $this->addSql("DROP TABLE notes_rates");
        $this->addSql("DROP TABLE notes_currencies");
    }
}
