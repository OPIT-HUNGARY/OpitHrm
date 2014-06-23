<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\LeaveBundle\Entity\LeaveRequest;
use Opit\Notes\LeaveBundle\Entity\Leave;
use Opit\Notes\LeaveBundle\Entity\StatesLeaveRequests;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveRequestFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class LeaveRequestFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        // Test for required user/status references first
        if (!$this->hasReference('admin') || !$this->hasReference('created')) {
            throw new \RuntimeException('Leave request fixtures require user/status bundle fixtures.');
        }

        // First travel request
        $admin = $this->getReference('admin');

        $leaveRequest1 = new LeaveRequest();
        $leaveRequest1->setEmployee($admin->getEmployee());
        $leaveRequest1->setGeneralManager($this->getReference('generalManager'));
        $leaveRequest1->setCreatedUser($admin);

        // Add Leaves
        $startDate = new \DateTime(date('Y-01-01'));
        $startDate->modify('next monday');
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P4D'));
        $days = $endDate->diff($startDate);

        $leave1 = new Leave();
        $leave1->setDescription('Winter vacation');
        $leave1->setStartDate($startDate);
        $leave1->setEndDate($endDate);
        $leave1->setNumberOfDays($days->format('%a'));
        $leave1->setCategory($this->getReference('leave-category-full-day'));

        $leaveRequest1->addLeaf($leave1);

        // Fake the state times
        $createdDateTime = new \DateTime();
        $createdDateTime->modify('last year dec last weekday');
        $createdDateTime->setTime('09', '45', '00');
        $forApprovalDateTime = clone $createdDateTime;
        $forApprovalDateTime->add(new \DateInterval('PT1H'));
        $approvedDateTime = clone $forApprovalDateTime;
        $approvedDateTime->modify('next weekday 16:13:00');

        // Add leave request states
        $leaveRequest1Status = new StatesLeaveRequests($this->getReference('created'));
        $leaveRequest1Status->setCreated($createdDateTime);
        $leaveRequest1Status->setCreatedUser($admin);
        $leaveRequest1->addState($leaveRequest1Status);

        $leaveRequest2Status = new StatesLeaveRequests($this->getReference('forApproval'));
        $leaveRequest2Status->setCreated($forApprovalDateTime);
        $leaveRequest2Status->setCreatedUser($admin);
        $leaveRequest1->addState($leaveRequest2Status);

        $leaveRequest3Status = new StatesLeaveRequests($this->getReference('approved'));
        $leaveRequest3Status->setCreated($approvedDateTime);
        $leaveRequest3Status->setCreatedUser($this->getReference('generalManager'));
        $leaveRequest1->addState($leaveRequest3Status);

        $manager->persist($leaveRequest1);

        // Second leave request
        $user = $this->getReference('user');
        $leaveRequest2 = new LeaveRequest();
        $leaveRequest2->setEmployee($user->getEmployee());
        $leaveRequest2->setGeneralManager($this->getReference('generalManager'));
        $leaveRequest2->setCreatedUser($user);

        // Add Leaves
        $startDate = new \DateTime();
        $startDate->modify('next month monday');
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P4D'));
        $days = $endDate->diff($startDate);

        $leave2 = new Leave();
        $leave2->setDescription('Family event');
        $leave2->setStartDate($startDate);
        $leave2->setEndDate($endDate);
        $leave2->setNumberOfDays($days->format('%a'));
        $leave2->setCategory($this->getReference('leave-category-full-day'));

        $leaveRequest2->addLeaf($leave2);

        // Fake the state times
        $createdDateTime = new \DateTime();
        $forApprovalDateTime = clone $createdDateTime;
        $forApprovalDateTime->add(new \DateInterval('PT15M'));

        // Add leave request states
        $leaveRequest4Status = new StatesLeaveRequests($this->getReference('created'));
        $leaveRequest4Status->setCreated($createdDateTime);
        $leaveRequest4Status->setCreatedUser($user);
        $leaveRequest2->addState($leaveRequest4Status);

        $leaveRequest5Status = new StatesLeaveRequests($this->getReference('forApproval'));
        $leaveRequest5Status->setCreated($forApprovalDateTime);
        $leaveRequest5Status->setCreatedUser($user);
        $leaveRequest2->addState($leaveRequest5Status);

        $manager->persist($leaveRequest2);

        // Third leave request, same user
        $leaveRequest3 = new LeaveRequest();
        $leaveRequest3->setEmployee($user->getEmployee());
        $leaveRequest3->setGeneralManager($this->getReference('generalManager'));
        $leaveRequest3->setCreatedUser($user);

        $startDate = new \DateTime();
        $startDate->modify('last week monday');
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P2D'));
        $days = $endDate->diff($startDate);

        $leave3 = new Leave();
        $leave3->setDescription('Cold');
        $leave3->setStartDate($startDate);
        $leave3->setEndDate($endDate);
        $leave3->setNumberOfDays($days->format('%a'));
        $leave3->setCategory($this->getReference('leave-category-sick-leave'));

        $leaveRequest3->addLeaf($leave3);

        // Fake the state times
        $createdDateTime = clone $endDate;
        $createdDateTime->modify('next weekday');

        // Add leave request states
        $leaveRequest6Status = new StatesLeaveRequests($this->getReference('created'));
        $leaveRequest6Status->setCreated($createdDateTime);
        $leaveRequest6Status->setCreatedUser($user);
        $leaveRequest3->addState($leaveRequest6Status);

        $manager->persist($leaveRequest3);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 30; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('dev', 'test');
    }
}
