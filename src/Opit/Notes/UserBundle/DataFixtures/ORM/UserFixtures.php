<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\UserBundle\Entity\User;
use Opit\Notes\UserBundle\Entity\Employee;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of User
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 *
 * @ORM\Table(name="notes_users")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\UserRepository")
 */
class UserFixtures extends AbstractDataFixture
{
    private $names = array('Janessa Hargrave', 'Jackelyn Norfleet', 'Keli Everman', 'Katherina Satterthwaite', 'Alix Fury', 'Darell Briseno', 'Emmitt Quattlebaum', 'Genna Castellano', 'Delia Highland', 'Malcolm Brew', 'Iesha Mcie', 'Marceline Simonton', 'Israel Hollander', 'Curt Hower', 'Guillermina Ponte', 'Adella Manross', 'Desirae Ciesielski', 'Winnifred Iglesias', 'Selina Schroeter', 'Johnathon Bonnell');

    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $factory = $this->container->get('security.encoder_factory');
        $user = new User();
        $encoder = $factory->getEncoder($user);

        $testAdmin = new User();
        $testAdmin->setUsername('admin');
        $testAdmin->setEmployee($this->createEmployee());
        $password = $encoder->encodePassword('admin', '');
        $testAdmin->setPassword($password);
        $testAdmin->setEmail('admin@mail.local');
        $testAdmin->setIsActive(1);
        if ('test' === $this->getCurrentEnvironment()) {
            $testAdmin->setIsFirstLogin(0);
        } else {
            $testAdmin->setIsFirstLogin(1);
        }
        $testAdmin->setLdapEnabled(0);
        $testAdmin->addGroup($this->getReference('admin-group'));

        $manager->persist($testAdmin);

        $testSystemAdmin = new User();
        $testSystemAdmin->setUsername('systemadmin');
        $testSystemAdmin->setEmployee($this->createEmployee());
        $password = $encoder->encodePassword('systemadmin', '');
        $testSystemAdmin->setPassword($password);
        $testSystemAdmin->setEmail('systemadmin@mail.local');
        $testSystemAdmin->setIsActive(1);
        if ('test' === $this->getCurrentEnvironment()) {
            $testSystemAdmin->setIsFirstLogin(0);
        } else {
            $testSystemAdmin->setIsFirstLogin(1);
        }
        $testSystemAdmin->setLdapEnabled(0);
        $testSystemAdmin->addGroup($this->getReference('system-admin-group'));

        $manager->persist($testSystemAdmin);

        if (in_array($this->getCurrentEnvironment(), array('dev', 'test'))) {

            for ($i = 0; $i < 10; $i++) {
                $testEmployee = $this->createEmployee();
                $username = substr($testEmployee->getEmployeeName(), strpos($testEmployee->getEmployeeName(), ' ')+1);
                $testUser = new User();
                $testUser->setUsername(strtolower($username));
                $testUser->setEmployee($testEmployee);
                $password = $encoder->encodePassword(strtolower($username), '');
                $testUser->setPassword($password);
                $testUser->setLdapEnabled(0);
                $testUser->setEmail(strtolower($username) . '@mail.local');
                $testUser->setIsActive(1);
                $testUser->setIsFirstLogin(1);
                $testUser->addGroup($this->getReference('user-group'));

                $manager->persist($testUser);
            }
            $this->addReference('testUser-user', $testUser);

            $testTeamManager = new User();
            $testTeamManager->setUsername('teamManager');
            $testTeamManager->setEmployee($this->createEmployee());
            $password = $encoder->encodePassword('teamManager', '');
            $testTeamManager->setPassword($password);
            $testTeamManager->setEmail('tm@mail.local');
            $testTeamManager->setIsActive(1);
            $testTeamManager->setIsFirstLogin(1);
            $testTeamManager->addGroup($this->getReference('team-manager-group'));
            $testTeamManager->addGroup($this->getReference('user-group'));
            $testTeamManager->setLdapEnabled(0);
            $manager->persist($testTeamManager);

            $testGeneralManager = new User();
            $testGeneralManager->setUsername('generalManager');
            $testGeneralManager->setEmployee($this->createEmployee());
            $password = $encoder->encodePassword('generalManager', '');
            $testGeneralManager->setPassword($password);
            $testGeneralManager->setEmail('gm@mail.local');
            $testGeneralManager->setIsActive(1);
            $testGeneralManager->setIsFirstLogin(1);
            $testGeneralManager->addGroup($this->getReference('general-manager-group'));
            $testGeneralManager->addGroup($this->getReference('user-group'));
            $testGeneralManager->setLdapEnabled(0);

            $manager->persist($testGeneralManager);

            $user = new User();
            $user->setUsername('user');
            $user->setEmployee($this->createEmployee());
            $password = $encoder->encodePassword('user', '');
            $user->setPassword($password);
            $user->setEmail('user@mail.local');
            $user->setIsActive(1);
            $user->setIsFirstLogin(1);
            $user->addGroup($this->getReference('user-group'));
            $user->setLdapEnabled(0);

            $manager->persist($user);

            $this->addReference('admin', $testAdmin);
            $this->addReference('user', $user);
            $this->addReference('generalManager', $testGeneralManager);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('prod', 'dev', 'test');
    }

    /**
     * Creates an employee instance
     *
     * @return \Opit\Notes\UserBundle\Entity\Employee
     */
    protected function createEmployee()
    {
        $birthTimestampRange= mt_rand(0, 820368000);
        $joinTimestampRange= mt_rand(1262304000, 1388534400);

        $birthDate = new \DateTime();
        $birthDate->setTimestamp($birthTimestampRange);
        $joinDate = new \DateTime();
        $joinDate->setTimestamp($joinTimestampRange);

        $workingHours = array(6, 8);

        $pos = array_rand($this->names);
        $name = $this->names[$pos];
        array_splice($this->names, $pos, 1);

        $employee = new Employee();
        $employee->setEmployeeName($name);
        $employee->setDateOfBirth($birthDate);
        $employee->setJoiningDate($joinDate);
        $employee->setNumberOfChildren(mt_rand(0, 4));
        $employee->setWorkingHours($workingHours[array_rand($workingHours)]);
        $employee->setBankAccountNumber(sprintf('%d-%d-%d', mt_rand(10000000, 99999999), mt_rand(10000000, 99999999), mt_rand(10000000, 99999999)));
        $employee->setBankName('Fictive Bank');
        $employee->setTaxIdentification(mt_rand(1000000000, 9999999999));

        return $employee;
    }
}
