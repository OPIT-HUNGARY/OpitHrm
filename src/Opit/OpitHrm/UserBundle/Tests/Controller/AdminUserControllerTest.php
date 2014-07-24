<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of AdminUserControllerTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class AdminUserControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Opit\OpitHrm\UserBundle\Entity\JobTitle
     */
    protected $jobTitle;

    /**
     * @var \Opit\OpitHrm\UserBundle\Entity\Groups
     */
    protected $group;

    /**
     * @var \Symfony\Component\BrowserKit\Client
     */
    protected $client;

    /**
     * Set up the test
     */
    public function setUp()
    {
        $this->client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'admin',
        ));
        $this->em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->jobTitle = $this->em->getRepository('OpitOpitHrmUserBundle:JobTitle')->findOneByTitle('CEO');
        $this->group = $this->em->getRepository('OpitOpitHrmUserBundle:Groups')->findOneByRole('ROLE_FINANCE');
    }

    /**
     * Set up before the class
     * Running the test database.
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Setup test db
        system(dirname(__FILE__) . '/../dbSetup.sh');
    }

    /**
     * testing index action.
     */
    public function testListJobTitleAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/admin/list/jobtitle'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testListJobTitleAction: The content-type is not html.'
        );
    }

    /**
     * testing showJobTitleForm action.
     */
    public function testShowJobTitleFormAction()
    {
        $crawler = $this->client->request(
            'GET',
            'secured/admin/show/jobtitle/' . $this->jobTitle->getId()
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowJobTitleFormAction: The content-type is not html.'
        );

        $crawler = $this->client->request(
            'GET',
            'secured/admin/show/jobtitle'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowJobTitleFormAction: The content-type is not html.'
        );
    }

    /**
     * testing addJobTitle action.
     */
    public function testAddJobTitleAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/admin/add/jobtitle/' . $this->jobTitle->getId()
        );

        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testAddJobTitleAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testAddJobTitleAction: The content-type is not a json.'
        );
    }

    /**
     * testing deleteJobTitle action.
     */
    public function testDeleteJobTitleAction()
    {
        // Giving a fake id number.
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/delete/jobtitle',
            array('delete-jobtitle[]' => 0)
        );
        $decodedJson = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testDeleteJobTitleAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testDeleteJobTitleAction: The content-type is not a json.'
        );
        $this->assertArrayHasKey('code', $decodedJson, 'testDeleteUserAction: Missing code array key.');
        $this->assertEquals('success', $decodedJson[0]['response'], 'testDeleteUserAction: Missing array value.');
    }

    /**
     * testing groupList action.
     */
    public function testGroupListAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/admin/groups/list'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testGroupListAction: The content-type is not html.'
        );
    }

    /**
     * testing groupsShow actin.
     */
    public function testGroupsShowAction()
    {
        // Create new role.
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/groups/show/new',
            array('value' => 'NEW_TEST_ROLE')
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testGroupsShowAction: The content-type of a new group is not html.'
        );
        
        // Modify existing role.
        $createdGroup = $this->em->getRepository('OpitOpitHrmUserBundle:Groups')->findOneByName('NEW_TEST_ROLE');

        $crawler = $this->client->request(
            'POST',
            '/secured/admin/groups/show/' . $createdGroup->getId(),
            array('value' => 'NEW_TEST_ROLE_MODIFIED')
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testGroupsShowAction: The content-type of an existing group is not html.'
        );

    }

    /**
     * testing deleteGroup adction.
     */
    public function testDeleteGroupAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/groups/delete',
            array('id' =>  $this->group->getId())
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testDeleteGroupAction: The content-type is not html.'
        );
    }
}
