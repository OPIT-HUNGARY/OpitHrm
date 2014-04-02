<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Opit\Notes\UserBundle\Entity\User;

/**
 * Description of GroupsRepositoryFunctionalTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class GroupsRepositoryFunctionalTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;
    
    /**
     * @var \Symfony\Component\BrowserKit\Client 
     */
    protected $client;
    /**
     * @var \Opit\Notes\UserBundle\Entity\User
     */
    protected $user;

    /**
     * Set up the testing
     */
    public function setUp()
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->user = $this->em->getRepository('OpitNotesUserBundle:User')->findOneByUsername('admin');
    }
        
    /**
     * test findAllGroupNamesArray method
     */
    public function testFindAllGroupNamesArray()
    {
        $groups = $this->em->getRepository("OpitNotesUserBundle:Groups")
                    ->findAllGroupNamesArray();
        
        $this->assertNotNull($groups, 'testFindAllGroupNamesArray: The given result is null.');
    }

    /**
     * test findUserGroupsArray method
     */
    public function testFindUserGroupsArray()
    {
        $userGroups = $this->em->getRepository("OpitNotesUserBundle:Groups")
                    ->findUserGroupsArray($this->user->getId());
        $this->assertNotNull($userGroups, 'testFindUserGroupsArray: The given result is null.');
    }
    
    /**
     * test findGroupsUsingIn method
     */
    public function testFindGroupsUsingIn()
    {
        $usedGroups = $this->em->getRepository("OpitNotesUserBundle:Groups")
                    ->findGroupsUsingIn(array(1,2,3,4,5,6,7));
        $this->assertNotNull($usedGroups, 'testFindGroupsUsingIn: The given result is null.');
    }
    
    /**
     * test findGroupsByNameUsingLike method
     */
    public function testFindGroupsByNameUsingLike()
    {
        $roles = array('ROLE_ADMIN');
        $groups = $this->em->getRepository("OpitNotesUserBundle:Groups")
                    ->findGroupsByNameUsingLike('admin', $roles);
        
        $this->assertNotNull($groups, 'testFindGroupsByNameUsingLike: The given result is null.');
    }
    
    /**
     * tear down
     */
    public function tearDown()
    {
        parent::tearDown();
        $this->em->close();
    }
}
