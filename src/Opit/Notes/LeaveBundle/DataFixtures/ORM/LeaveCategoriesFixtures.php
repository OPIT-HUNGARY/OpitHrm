<?php

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

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
use Opit\Notes\LeaveBundle\Entity\LeaveCategory;
use Opit\Notes\LeaveBundle\Entity\LeaveDuration;
use Opit\Notes\LeaveBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveCategoriesFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveCategoriesFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $fullDay = $manager->getRepository('OpitNotesLeaveBundle:LeaveDuration')->find(LeaveDuration::FULLDAY);
        $halfDay = $manager->getRepository('OpitNotesLeaveBundle:LeaveDuration')->find(LeaveDuration::HALFDAY);
        
        $categories = array(
            'Full day' => array('description' => 'Employee takes full day off.', 'duration' => $fullDay),
            'Morning half day' => array('description' => 'Employee takes morning half day off.', 'duration' => $halfDay),
            'Afternoon half day' => array('description' => 'Employee takes afternoon half day off.', 'duration' => $halfDay),
            'Sick leave' => array('description' => 'Employee takes sick leave.', 'duration' => $fullDay),
            'Unpaid leave' => array('description' => 'Employee takes unpaid leave.', 'duration' => $fullDay)
        );

        foreach ($categories as $key => $value) {
            $holidayCategory = new LeaveCategory();
            $holidayCategory->setName($key);
            $holidayCategory->setDescription($value['description']);
            $holidayCategory->setLeaveDuration($value['duration']);
            $manager->persist($holidayCategory);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 14; // the order in which fixtures will be loaded
    }
    
    /**
     * 
     * @return array
     */
    protected function getEnvironments()
    {
        return array('dev', 'prod', 'test');
    }
}
