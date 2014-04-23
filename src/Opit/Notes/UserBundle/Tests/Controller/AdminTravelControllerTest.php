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
 * Description of AdminTravelControllerTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class AdminTravelControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * @var \Opit\Notes\TravelBundle\Entity\TEPerDiem 
     */
    protected $perDiem;
    
    /**
     * @var \Opit\Notes\TravelBundle\Entity\TEExpenseType  
     */
    protected $expenseType;
    
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

        $adminUser = $this->em->getRepository('OpitNotesUserBundle:User')->findOneByUsername('admin');
        $adminUser->setIsFirstLogin(false);
        $this->em->persist($adminUser);
        $this->em->flush();
        
        $this->perDiem = $this->em->getRepository('OpitNotesTravelBundle:TEPerDiem')->findOneByHours('8');
        $this->expenseType = $this->em->getRepository('OpitNotesTravelBundle:TEExpenseType')->findOneByName('Other');
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
     * test index action
     */
    public function testListExpenseTypeAction()
    {
        $crawler = $this->client->request(
            'GET',
            '/secured/admin/travelexpensetype/list'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testListExpenseTypeAction: The content-type is not html.'
        );
    }
    
    /**
     * testing ExpenseTypeShow action.
     */
    public function testExpenseTypeShowAction()
    {
        // Existing per diem.
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/travelexpensetype/show/' . $this->expenseType->getId()
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testExpenseTypeShowAction: The content-type is not html.'
        );
        
        // New expense type
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/travelexpensetype/show/new'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testExpenseTypeShowAction: The content-type is not html.'
        );
    }
    
    /**
     * testing deleteExpenseType action.
     */
    public function testDeleteExpenseTypeAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/expensetype/delete',
            array('id' => '3')
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testDeleteExpenseTypeAction: The content-type is not html.'
        );
    }
    
    /**
     * testing listPerDiem action.
     */
    public function testListPerDiemAction()
    {
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/list/perdiem'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testListPerDiemAction: The content-type is not html.'
        );
    }
    
    /**
     * testing showPerDiem action.
     */
    public function testShowPerDiemAction()
    {
        // Existing per diem
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/show/perdiem/' . $this->perDiem->getId()
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowPerDiemAction: The content-type is not html.'
        );
        
        // New per diem
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/show/perdiem/new'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testShowPerDiemAction: The content-type is not html.'
        );
    }
    
    /**
     * testing savePerDiem action.
     */
    public function testSavePerDiemAction()
    {
        // Empty request
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/save/perdiem'
        );
        $this->assertJson(
            $this->client->getResponse()->getContent(),
            'testSavePerDiemAction: The response\'s content is not a JSON object.'
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'testSavePerDiemAction: The content-type is not a json.'
        );
        
        // Filled up request
        $crawler = $this->client->request(
            'POST',
            '/secured/admin/save/perdiem',
            array(
                'perdiem' => array(
                    0 => array(
                        'id' => 1,
                        'hours' => 14,
                        'amount' => 24
                    ),
                    1 => array(
                        'id' => null,
                        'hours' => 12,
                        'amount' => 12
                    )
                )
            )
        );
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'testSavePerDiemAction: The content-type is not html.'
        );
    }
}
