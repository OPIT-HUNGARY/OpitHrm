<?php

/*
 * This file is part of the NOTES bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Description of UserControllerTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class UserControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Opit\Notes\UserBundle\Entity\User
     */
    protected $user;

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

        $this->user = $this->em->getRepository('OpitNotesUserBundle:User')->findOneByUsername('admin');
    }

    /**
     * test list action
     */
    public function testlistAction()
    {

        $crawler = $this->client->request(
            'GET',
            '/secured/user/list'
        );
        $content = $this->client->getResponse()->getContent();

        $this->assertNotEmpty($content, 'testlistAction: The content is empty');
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertGreaterThanOrEqual(
            1000,
            strlen($content),
            'testlistAction: The length of content is less than 1000.'
        );

        // With search options
        $crawler = $this->client->request(
            'POST',
            '/secured/user/list',
            array('issearch' => '1',  'search' => array('username' => 'admin'))
        );
        $content = $this->client->getResponse()->getContent();

        $this->assertNotEmpty($content, 'testlistAction: The content is empty');
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'));
        $this->assertGreaterThanOrEqual(
            1000,
            strlen($content),
            'testlistAction: The length of content is less than 1000.'
        );
    }

    /**
     * testing showUserForm action.
     */
    public function testShowUserFormAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/user/show/' . $this->user->getId()
        );
        $content = $this->client->getResponse()->getContent();

        $this->assertNotEmpty($content, 'testShowUserFormAction: The content is empty');
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowUserFormAction: The content-type is not html.'
        );
        $this->assertGreaterThanOrEqual(
            1000,
            strlen($content),
            'testShowUserFormAction: The length of content is less than 1000.'
        );
    }

    /**
     * testing adduser action.
     */
    public function testAddUserAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/user/add/' . $this->user->getId()
        );

        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Type', 'application/json'));

        $crawler = $this->client->request(
            'POST',
            '/secured/user/add/' . $this->user->getId(),
            array(
                'user' => array(
                    'username' => $this->user->getUsername(),
                    'email' => $this->user->getEmail(),
                    'employeeName' => $this->user->getEmployee()->getEmployeeName(),
                    'taxIdentification' => $this->user->getEmployee()->getTaxIdentification(),
                    'bankAccountNumber' => $this->user->getEmployee()->getBankAccountNumber(),
                    'bankName' => $this->user->getEmployee()->getBankName(),
                    'userId' =>  $this->user->getId(),
                    'isActive' => $this->user->getIsActive()
                )
            )
        );
        $decodedJson = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testAddUserAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testAddUserAction: The content-type is not a json.'
        );
        $this->assertArrayHasKey(
            'response',
            $decodedJson[0],
            'testAddUserAction: Missing response array key.'
        );
        $this->assertEquals(
            'error',
            $decodedJson[0]['response'],
            'testAddUserAction: Missing array value.'
        );
    }

    /**
     * testing deleteUser action.
     */
    public function testDeleteUserAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/user/delete',
            array('delete-user[]' => 0)
        );
        $decodedJson = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testDeleteUserAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testDeleteUserAction: The content-type is not a json.'
        );
        $this->assertArrayHasKey('code', $decodedJson, 'testDeleteUserAction: Missing code array key.');
        $this->assertEquals(
            'success',
            $decodedJson[0]['response'],
            'testDeleteUserAction: Missing array value.'
        );
    }

    /**
     * testing showPassword action.
     */
    public function testShowPasswordAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/user/changepassword'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowPasswordAction: The content-type is not html.'
        );
    }

    /**
     * testing showChangePassword action.
     */
    public function testShowChangePasswordAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/user/show/password/' . $this->user->getId()
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowChangePasswordAction: The content-type is not html.'
        );
    }

    /**
     * testing showUpdatePassword action.
     */
    public function testShowUpdatePasswordAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/user/update/password/' . $this->user->getId()
        );
        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testShowUpdatePasswordAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testShowUpdatePasswordAction: The content-type is not a json.'
        );
    }

    /**
     * testing the resetPassword action.
     *
     * This should be the last method, because this is reset the password for the admin.
     */
    public function testResetPasswordAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/user/password/reset',
            array('id' => $this->user->getId())
        );

        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testResetPasswordAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testResetPasswordAction: The content-type is not a json.'
        );
    }
}
