<?php

/*
 * This file is part of the OPIT-HRM project.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Tests\Voter;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Opit\OpitHrm\LeaveBundle\Security\Authorization\Voter\LeaveAccessVoter;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Description of AccessVoterTest
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 */
class LeaveAccessVoterTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Set up before the class
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        // Setup test db
        system(dirname(__FILE__) . '/../dbSetup.sh');
    }

    /**
     * Set up for the testing
     */
    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testVote()
    {
        $generalManager = $this->em->getRepository('OpitOpitHrmUserBundle:User')->findByUsername('generalManager');
        $admin = $this->em->getRepository('OpitOpitHrmUserBundle:User')->findByUsername('admin');
        $user = $this->em->getRepository('OpitOpitHrmUserBundle:User')->findByUsername('user');

        $leaveRequests = $this->em->getRepository('OpitOpitHrmLeaveBundle:LeaveRequest')->findByEmployee($user);
        $leaveRequest = current($leaveRequests);

        $leaveAccessVoter = new LeaveAccessVoter($this->em);

        $firewall = 'secured_area';

        $gmToken = new UsernamePasswordToken('generalManager', null, $firewall, array('ROLE_ADMIN'));
        $gmToken->setUser(current($generalManager));

        $adminToken = new UsernamePasswordToken('generalManager', null, $firewall, array('ROLE_GENERAL_MANAGER'));
        $adminToken->setUser(current($admin));

        $userToken = new UsernamePasswordToken('user', null, $firewall, array('ROLE_USER'));
        $userToken->setUser(current($user));

        // Check if gm can access lr
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($gmToken, $leaveRequest, array('view')),
            'Vote: General manager not can view leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if admin can access lr
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($adminToken, $leaveRequest, array('view')),
            'Vote: Admin can not view leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if user can access lr
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($userToken, $leaveRequest, array('view')),
            'Vote: User can view leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if gm can edit lr
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $leaveAccessVoter->vote($gmToken, $leaveRequest, array('edit')),
            'Vote: General manager can edit leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if admin can edit lr
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $leaveAccessVoter->vote($adminToken, $leaveRequest, array('edit')),
            'Vote: Admin can edit leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if user can edit lr
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $leaveAccessVoter->vote($userToken, $leaveRequest, array('edit')),
            'Vote: User can edit leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if gm can delete lr
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($gmToken, $leaveRequest, array('delete')),
            'Vote: General manager can not edit leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if admin can delete lr
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($adminToken, $leaveRequest, array('delete')),
            'Vote: Admin can not edit leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if user can delete lr
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($userToken, $leaveRequest, array('delete')),
            'Vote: User can edit leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if gm can change lr status
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($gmToken, $leaveRequest, array('status')),
            'Vote: General manager can change status leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if admin can change lr status
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $leaveAccessVoter->vote($adminToken, $leaveRequest, array('status')),
            'Vote: Admin can change status leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );

        // Check if user can change lr status
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $leaveAccessVoter->vote($userToken, $leaveRequest, array('status')),
            'Vote: User can change status leave request ' . $leaveRequest->getLeaveRequestId() . '.'
        );
    }
}
