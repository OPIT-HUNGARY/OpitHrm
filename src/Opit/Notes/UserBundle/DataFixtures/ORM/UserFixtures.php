<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\UserBundle\Entity\User;

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
class UserFixtures implements FixtureInterface 
{
    
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager) 
    {
        for($i = 0; $i < 10; $i++) {
            $testUser = new User();
            $testUser->setUsername("test".$i."Name");
            $testUser->setPassword("test".$i."Password");
            $testUser->setSalt("setSalt");
            $testUser->setEmail("mymail".$i."@mail.com");
            $testUser->setIsActive(1);

            $manager->persist($testUser);
        }
        
        $manager->flush();
    }

//put your code here
}
