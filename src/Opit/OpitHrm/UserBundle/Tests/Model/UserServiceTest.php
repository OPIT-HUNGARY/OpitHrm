<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Tests\Model;

use Opit\OpitHrm\UserBundle\Model\UserService;

/**
 * Description of UserServiceTest
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class UserServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Opit\OpitHrm\UserBundle\Model\UserService 
     */
    protected $userService;
    
    /**
     * @var \Symfony\Component\Security\Core\User\UserInterface
     */
    protected $user;
    
    /**
     * @var \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface
     */
    protected $encoder;
    
    /**
     * set up the testing.
     */
    public function setUp()
    {
        // Mocking the User Interface.
        $user = $this->getMockBuilder('Symfony\Component\Security\Core\User\UserInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->any())
            ->method('getSalt')
            ->will($this->returnValue(''));
        
        // Setting the User propterty.
        $this->user = $user;
        
        // Mocking the password encoder.
        $passwordEncoder = $this->getMockBuilder('Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder')
            ->disableOriginalConstructor()
            ->getMock();
        $passwordEncoder->expects($this->any())
            ->method('encodePassword')
            ->will($this->returnValue('password'));
        
        // Mocking the Encoder Factory Interface.
        $innerFactory = $this->getMock('Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface');
        $innerFactory->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($passwordEncoder));
        // Set the Encoder property.
        $this->encoder = $innerFactory;
             
        // Mocking the Email Manager.
        $mailer = $this->getMockBuilder('Opit\OpitHrm\CoreBundle\Email\EmailManager')
            ->disableOriginalConstructor()
            ->getMock();
        $mailer->expects($this->any())
            ->method('setRecipient');
        $mailer->expects($this->any())
            ->method('setSubject');
        $mailer->expects($this->any())
            ->method('setBodyByTemplate');
        
        //Set the Email manager property.
        $this->mail = $mailer;
        
        // Set the UserService property.
        $this->userService = new UserService($mailer, $innerFactory);
    }
    
    /**
     * testing generatePassword method.
     */
    public function testGeneratePassword()
    {
        $password = $this->userService->generatePassword();
        
        $this->assertNotNull($password, 'testGeneratePassword: The password is null.');
        $this->assertEquals(10, strlen($password), 'testGeneratePassword: The password is not 10 character length.');
    }
    
    /**
     * testing encodePassword method.
     */
    public function testEncodePassword()
    {
        $password = $this->userService->encodePassword($this->user);
       
        $this->assertNotNull($password, 'testEncodePassword: The password is null.');
        $this->assertEquals(
            'password',
            $password,
            'testEncodePassword: The expected and the given password are not equal.'
        );
    }
    
    /**
     * testing sendNewPasswordMail method.
     * 
     * This method does not have any return value.
     */
    public function testSendNewPasswordMail()
    {
        // Mocking a User .
        $user = $this->getMockBuilder('Opit\OpitHrm\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $user->expects($this->any())
            ->method('getEmail')
            ->will($this->returnValue('bota@opit.hu'));
        
        $this->userService->sendNewPasswordMail($user);
        $this->userService->sendNewPasswordMail($user, true);
    }
}
