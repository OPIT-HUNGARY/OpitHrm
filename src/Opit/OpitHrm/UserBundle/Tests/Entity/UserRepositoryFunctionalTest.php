<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Opit\OpitHrm\UserBundle\Entity\User;
use Opit\OpitHrm\UserBundle\Entity\Employee;

/**
 * Description of UserRepositoryFunctionalTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class UserRepositoryFunctionalTest extends WebTestCase
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
     * @var \Opit\OpitHrm\UserBundle\Entity\User
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

        $this->user = $this->em->getRepository('OpitOpitHrmUserBundle:User')->findOneByUsername('admin');
    }

    /**
     * test LoadUserByUsername method
     */
    public function testLoadUserByUsername()
    {
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->loadUserByUsername('admin');

        $this->assertNotNull($user, 'testLoadUserByUsername: The given result is null.');
    }

    /**
     * test RefreshUser method
     */
    public function testRefreshUser()
    {
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->refreshUser($this->user);

        $this->assertNotNull($user, 'testrefreshUser: The given result is null.');
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function testLoadUserByUsernameException()
    {
        $this->em->getRepository("OpitOpitHrmUserBundle:User")->loadUserByUsername('test');
    }

    /**
     * test findAll method
     */
    public function testfindAll()
    {
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->findAll($this->user);

        $this->assertNotNull($user, 'testfindAll: The given result is null.');
    }

    /**
     * test findUsersByPropertyUsingLike method
     */
    public function testFindUsersByPropertyUsingLike()
    {
        // Set the parameters.
        $parameters = array(
            'search' => array('username' => 'admin'),
            'order' => array('field' => 'username', 'dir' => 'ASC')
        );
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->findUsersByPropertyUsingLike($parameters, 0, 1);

        $this->assertNotNull($user, 'testFindUsersByPropertyUsingLike: The given result is null.');
    }

    /**
     * test findUsersUsingIn method
     */
    public function testFindUsersUsingIn()
    {
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->findUsersUsingIn($this->user->getId());

        $this->assertNotNull($user, 'testFindUsersUsingIn: The given result is null.');
    }

    /**
     * test findUserByEmployeeNameUsingLike method
     */
    public function testFindUserByEmployeeNameUsingLike()
    {
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->findUserByEmployeeNameUsingLike($this->user->getEmployee()->getEmployeeName());

        $this->assertNotNull($user, 'testFindUserByEmployeeNameUsingLike: The given result is null.');
    }

    /**
     * test findUserByEmployeeNameUsingLike method
     */
    public function testDeleteUsersByIds()
    {
        $newEmployee = new Employee();
        $newEmployee->setDateOfBirth(new \DateTime('1990-02-01'));
        $newEmployee->setJoiningDate(new \DateTime('2009-03-01'));
        $newEmployee->setNumberOfChildren(0);
        $newEmployee->setEmployeeName('employee');
        $newEmployee->setWorkingHours(8);
        $newEmployee->setBankAccountNumber('00000000-11468115');
        $newEmployee->setBankName('OTP');

        // Create a new user.
        $newUser = new User();
        $newUser->setUsername('test1');
        $newUser->setEmail('test1@mail.hu');
        $newUser->setEmployee($newEmployee);
        $newUser->setSalt('');
        $newUser->setPassword('test1');
        $newUser->setIsActive(1);
        $newUser->setLdapEnabled(0);

        $newUser->setIsFirstLogin(0);
        // Save the new user into the local test database.
        $this->em->persist($newUser);
        $this->em->flush();
        // Get the saved user.
        $testUser = $this->em->getRepository("OpitOpitHrmUserBundle:User")->findOneByUsername('test1');

        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
                    ->deleteUsersByIds($testUser->getId());

        $this->assertNotNull($user, 'testDeleteUsersByIds: The given result is null.');
    }

    /**
     * test getPaginaton method
     */
    public function testGetPaginaton()
    {
        $user = $this->em->getRepository("OpitOpitHrmUserBundle:User")
            ->getPaginaton(0, 1);

        $this->assertNotNull($user, 'testGetPaginaton: The given result is null.');
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
