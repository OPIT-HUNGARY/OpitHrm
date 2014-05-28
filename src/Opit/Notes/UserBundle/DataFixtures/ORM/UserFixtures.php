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
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $factory = $this->container->get('security.encoder_factory');
        $user = new User();
        $encoder = $factory->getEncoder($user);
        
        $employee = new Employee();
        $employee->setDateOfBirth(new \DateTime('1990-02-01'));
        $employee->setJoiningDate(new \DateTime('2009-03-01'));
        $employee->setNumberOfChildren(0);
        $employee->setWorkingHours(8);
        $employee->setEmployeeName('employee');
        $employee->setWorkingHours(8);

        $testAdmin = new User();
        $testAdmin->setUsername('admin');
        $testAdmin->setEmployee($employee);
        $password = $encoder->encodePassword('admin', '');
        $testAdmin->setPassword($password);
        $testAdmin->setSalt('');
        $testAdmin->setEmail('admin@mail.com');
        $testAdmin->setIsActive(1);
        if ('test' === $this->getCurrentEnvironment()) {
            $testAdmin->setIsFirstLogin(0);
        } else {
            $testAdmin->setIsFirstLogin(1);
        }
        $testAdmin->setLdapEnabled(0);
        $testAdmin->addGroup($this->getReference('admin-group'));
        $testAdmin->setBankAccountNumber('11112222-99999999-99999999');
        $testAdmin->setBankName('Fictive Bank');
        $testAdmin->setTaxIdentification('3888888888');
        $manager->persist($testAdmin);
        
        if ('dev' === $this->getCurrentEnvironment()) {
            $employee2 = new Employee();
            $employee2->setDateOfBirth(new \DateTime('1990-02-01'));
            $employee2->setJoiningDate(new \DateTime('2009-03-01'));
            $employee2->setNumberOfChildren(0);
            $employee2->setWorkingHours(12);
            $employee2->setEmployeeName('employee2');
            $employee2->setWorkingHours(6);

            for ($i = 0; $i < 10; $i++) {
                $testUser = new User();
                $testUser->setUsername('test' . $i . 'Name');
                $testUser->setEmployee(clone $employee2);
                $password = $encoder->encodePassword('test' . $i . 'Password', '');
                $testUser->setPassword($password);
                $testUser->setSalt('');
                $testUser->setLdapEnabled(0);
                $testUser->setEmail('mymail' . $i . '@mail.com');
                $testUser->setIsActive(1);
                $testUser->setIsFirstLogin(1);
                $testUser->addGroup($this->getReference('user-group'));
                $testUser->setBankAccountNumber('11112222-22223333-4444'. $i . $i . $i . $i);
                $testUser->setBankName('Fictive Bank');
                $testUser->setTaxIdentification('843'. $i . $i%2 . $i%4 . '45' . $i%3 .$i%5);
                $manager->persist($testUser);
            }
            $this->addReference('testUser-user', $testUser);

            $testTeamManager = new User();
            $testTeamManager->setUsername('teamManager');
            $testTeamManager->setEmployee(clone $employee->setEmployeeName('teamManager'));
            $password = $encoder->encodePassword('teamManager', '');
            $testTeamManager->setPassword($password);
            $testTeamManager->setSalt('');
            $testTeamManager->setEmail('tm@mail.com');
            $testTeamManager->setIsActive(1);
            $testTeamManager->setIsFirstLogin(1);
            $testTeamManager->addGroup($this->getReference('team-manager-group'));
            $testTeamManager->addGroup($this->getReference('user-group'));
            $testTeamManager->setBankAccountNumber('11112222-99999999-99999999');
            $testTeamManager->setBankName('Fictive Bank');
            $testTeamManager->setLdapEnabled(0);
            $testTeamManager->setTaxIdentification('8888188888');
            $manager->persist($testTeamManager);

            $testGeneralManager = new User();
            $testGeneralManager->setUsername('generalManager');
            $testGeneralManager->setEmployee(clone $employee->setEmployeeName('generalManager'));
            $password = $encoder->encodePassword('generalManager', '');
            $testGeneralManager->setPassword($password);
            $testGeneralManager->setSalt('');
            $testGeneralManager->setEmail('gm@mail.com');
            $testGeneralManager->setIsActive(1);
            $testGeneralManager->setIsFirstLogin(1);
            $testGeneralManager->addGroup($this->getReference('general-manager-group'));
            $testGeneralManager->addGroup($this->getReference('user-group'));
            $testGeneralManager->setBankAccountNumber('11112222-99999999-99999999');
            $testGeneralManager->setBankName('Fictive Bank');
            $testGeneralManager->setLdapEnabled(0);
            $testGeneralManager->setTaxIdentification('8888888288');
            $manager->persist($testGeneralManager);
            
            $user = new User();
            $user->setUsername('user');
            $user->setEmployee(clone $employee->setEmployeeName('user'));
            $password = $encoder->encodePassword('user', '');
            $user->setPassword($password);
            $user->setSalt('');
            $user->setEmail('user@mail.com');
            $user->setIsActive(1);
            $user->setIsFirstLogin(true);
            $user->addGroup($this->getReference('user-group'));
            $user->setBankAccountNumber('11112222-99999999-11999999');
            $user->setBankName('Fictive Bank');
            $user->setLdapEnabled(0);
            $user->setTaxIdentification('8888888211');
            $manager->persist($user);
            
            $this->setReference('user', $user);
            $this->setReference('generalManager', $testGeneralManager);
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
}
