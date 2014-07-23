<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveRequest;
use Opit\OpitHrm\LeaveBundle\Entity\Leave;
use Opit\OpitHrm\LeaveBundle\Entity\LeaveRequestGroup;
use Opit\OpitHrm\LeaveBundle\Entity\StatesLeaveRequests;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of MassLeaveRequestFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
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

        $service = $this->container->get('opit.model.leave_request');
        $admin = $this->getReference('admin');

        // Create Datetimes.
        $startDate = new \DateTime(date('Y-12-24'));
        $endDate = new \DateTime(date('Y-01-01'));
        $endDate->modify('+1 year');
        // Notice: The service is based on LeaveDatesFixtures, ensure current year's public holidays are present.
        $days = $service->countLeaveDays($startDate, $endDate);

        // Create leave
        $leave = new Leave();
        $leave->setDescription('Christmas vacation');
        $leave->setStartDate($startDate);
        $leave->setEndDate($endDate);
        $leave->setNumberOfDays($days);
        $leave->setCategory($this->getReference('leave-category-full-day'));

        // Create leave request group.
        $leaveRequestGroup = new LeaveRequestGroup();
        $manager->persist($leaveRequestGroup);

        // Create First LeaveRequest.
        $leaveRequest1 = new LeaveRequest();
        $leaveRequest1->setEmployee($admin->getEmployee());
        $leaveRequest1->setCreatedUser($this->getReference('generalManager'));
        $leaveRequest1->setGeneralManager($this->getReference('generalManager'));
        $leaveRequest1->setIsMassLeaveRequest(false);
        $leaveRequest1->setLeaveRequestGroup($leaveRequestGroup);
        $leaveRequest1->addLeaf($leave);

        // Create mass leave request, persist first.
        $massLeaveRequest = clone $leaveRequest1;
        $massLeaveRequest->setEmployee($this->getReference('generalManager')->getEmployee());
        $massLeaveRequest->setCreatedUser($this->getReference('generalManager'));
        $massLeaveRequest->setIsMassLeaveRequest(true);
        $massLeaveRequest->setLeaveRequestGroup($leaveRequestGroup);
        $massLeaveRequest->addLeaf(clone $leave);

        $manager->persist($massLeaveRequest);

        // Add leave request states
        $leaveRequest1Status = new StatesLeaveRequests($this->getReference('approved'));
        $leaveRequest1Status->setCreated(new \DateTime());
        $leaveRequest1Status->setCreatedUser($this->getReference('generalManager'));
        $leaveRequest1->addState($leaveRequest1Status);

        $manager->persist($leaveRequest1);

        // Create Second leave request.
        $leaveRequest2 = clone $leaveRequest1;
        $leaveRequest2->setEmployee($this->getReference('user')->getEmployee());
        $leaveRequest2->setCreatedUser($this->getReference('generalManager'));
        $leaveRequest2->setIsMassLeaveRequest(false);
        $leaveRequest2->setLeaveRequestGroup($leaveRequestGroup);
        $leaveRequest2->addLeaf(clone $leave);

        $leaveRequest2->addState(clone $leaveRequest1Status);

        $manager->persist($leaveRequest2);

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
