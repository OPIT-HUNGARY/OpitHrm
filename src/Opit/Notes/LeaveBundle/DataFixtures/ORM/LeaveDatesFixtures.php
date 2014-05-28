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
use Opit\Notes\LeaveBundle\Entity\LeaveDate;
use Opit\Notes\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveDatesFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 */
class LeaveDatesFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $dates = array(
            '2014-01-01' => 'Bank holidays',
            '2014-03-15' => 'Bank holidays',
            '2014-04-21' => 'Bank holidays',
            '2014-05-01' => 'Bank holidays',
            '2014-05-02' => 'Bank holidays',
            '2014-06-09' => 'Bank holidays',
            '2014-08-20' => 'Bank holidays',
            '2014-10-23' => 'Bank holidays',
            '2014-10-24' => 'Bank holidays',
            '2014-11-01' => 'Bank holidays',
            '2014-12-24' => 'Bank holidays',
            '2014-12-25' => 'Bank holidays',
            '2014-12-26' => 'Bank holidays',
            '2014-05-10' => 'Weekend working days',
            '2014-10-18' => 'Weekend working days',
            '2014-12-13' => 'Weekend working days',
            '2013-01-01' => 'Bank holidays',
            '2013-03-15' => 'Bank holidays',
            '2013-05-01' => 'Bank holidays',
            '2013-08-20' => 'Bank holidays',
            '2013-10-23' => 'Bank holidays',
            '2013-11-01' => 'Bank holidays',
            '2013-12-24' => 'Bank holidays',
            '2013-12-25' => 'Bank holidays',
            '2013-12-26' => 'Bank holidays',
        );

        foreach ($dates as $key => $value) {
            $holidayDate = new LeaveDate();
            $holidayDate->setHolidayDate(new \DateTime($key));
            $holidayDate->setHolidayType($this->getReference($value));
            $manager->persist($holidayDate);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 11; // the order in which fixtures will be loaded
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
