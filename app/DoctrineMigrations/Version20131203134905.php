<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Opit\OpitHrm\UserBundle\Entity\User;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20131203134905 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_users DROP discr");

        $factory = $this->container->get('security.encoder_factory');
        $user = new User();
        $encoder = $factory->getEncoder($user);

        // Add required system groups and admin user
        $this->addSql("INSERT INTO opithrm_groups (id, name, role) VALUES (1,'Admin','ROLE_ADMIN'),(2,'User','ROLE_USER'),(3,'General manager','ROLE_GENERAL_MANAGER'),(4,'Team manager','ROLE_TEAM_MANAGER')");
        $this->addSql("INSERT INTO opithrm_users (id, username, salt, password, email, is_active, employeeName) VALUES (1,'admin','','" . $encoder->encodePassword('admin', '') . "','admin@mail.com',1,'Admin')");
        $this->addSql("INSERT INTO opithrm_users_groups (user_id, groups_id) values (1,1)");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql", "Migration can only be executed safely on 'mysql'.");

        $this->addSql("ALTER TABLE opithrm_users ADD discr VARCHAR(255) NOT NULL");
    }
}
