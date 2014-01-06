<?php

/*
 * This file is part of the Notes bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\UserBundle\Entity\User;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * Description of User
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 * 
 * @ORM\Table(name="notes_users")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\UserRepository")
 */
class UserFixtures extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $factory = $this->container->get('security.encoder_factory');
        $user = new User();
        $encoder = $factory->getEncoder($user);

        for ($i = 0; $i < 10; $i++) {
            $testUser = new User();
            $testUser->setUsername("test".$i."Name");
            $password = $encoder->encodePassword("test".$i."Password", "");
            $testUser->setPassword($password);
            $testUser->setSalt("");
            $testUser->setEmail("mymail".$i."@mail.com");
            $testUser->setEmployeeName("empname".$i);
            $testUser->setIsActive(1);
            $testUser->addGroup($this->getReference('admin-group'));
            $testUser->setBankAccountNumber("11112222-22223333-4444".$i.$i.$i.$i);
            $testUser->setBankName("Fictive Bank");
            $testUser->setTaxIdentification("843". $i . $i%2 . $i%4 ."4". $i%3 .$i%5);
            $manager->persist($testUser);
        }
        $this->addReference('testUser-user', $testUser);

        $testUser = new User();
        $testUser->setUsername("admin");
        $password = $encoder->encodePassword("admin", "");
        $testUser->setPassword($password);
        $testUser->setSalt("");
        $testUser->setEmail("admin@mail.com");
        $testUser->setEmployeeName("admin");
        $testUser->setIsActive(1);
        $testUser->addGroup($this->getReference('admin-group'));
        $testUser->setBankAccountNumber("11112222-99999999-99999999");
        $testUser->setBankName("Fictive Bank");
        $testUser->setTaxIdentification("888888888");
        $manager->persist($testUser);
        
        
        $manager->flush();
    }
    
    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
