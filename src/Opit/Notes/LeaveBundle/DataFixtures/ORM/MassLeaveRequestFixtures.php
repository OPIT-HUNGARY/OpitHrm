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
use Opit\Notes\LeaveBundle\Entity\LeaveRequestGroup;
use Opit\Notes\LeaveBundle\Entity\StatesLeaveRequests;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of MassLeaveRequestFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class MassLeaveRequestFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        // Test for required leaveRequestss references first
        if (!$this->hasReference('admin') || !$this->hasReference('user') || !$this->hasReference('generalManager')) {
            throw new \RuntimeException('Mass leave request fixtures require user fixtures.');
        }

        $admin = $this->getReference('admin');

        // Create Datetimes.
        $startDate = new \DateTime(date('Y-12-27'));
        $startDate->modify('next monday');
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P4D'));
        $days = $endDate->diff($startDate);

        // Create leave
        $leave = new Leave();
        $leave->setDescription('Christmas vacation');
        $leave->setStartDate($startDate);
        $leave->setEndDate($endDate);
        $leave->setNumberOfDays($days->format('%a'));
        $leave->setCategory($this->getReference('leave-category-full-day'));

        // Create leave request group.
        $leaveRequestGroup = new LeaveRequestGroup();
        $manager->persist($leaveRequestGroup);

        // Create First LeaveRequest.
        $leaveRequest1 = new LeaveRequest();
        $leaveRequest1->setEmployee($admin->getEmployee());
        $leaveRequest1->setGeneralManager($this->getReference('generalManager'));
        $leaveRequest1->setCreatedUser($admin);
        $leaveRequest1->setIsMassLeaveRequest(false);
        $leaveRequest1->setLeaveRequestGroup($leaveRequestGroup);
        $leaveRequest1->addLeaf($leave);

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

        // Create Second leave request.
        $leaveRequest2 = clone $leaveRequest1;
        $leaveRequest2->setEmployee($this->getReference('user')->getEmployee());
        $leaveRequest2->setIsMassLeaveRequest(false);
        $leaveRequest2->setLeaveRequestGroup($leaveRequestGroup);
        $leaveRequest2->addLeaf(clone $leave);

        $leaveRequest2->addState(clone $leaveRequest1Status);
        $leaveRequest2->addState(clone $leaveRequest2Status);
        $leaveRequest2->addState(clone $leaveRequest3Status);

        $manager->persist($leaveRequest2);

        // Create Mass leave request.
        $massLeaveRequest = clone $leaveRequest1;
        $massLeaveRequest->setEmployee($this->getReference('generalManager')->getEmployee());
        $massLeaveRequest->setIsMassLeaveRequest(true);
        $massLeaveRequest->setLeaveRequestGroup($leaveRequestGroup);
        $massLeaveRequest->addLeaf(clone $leave);

        $manager->persist($massLeaveRequest);

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
