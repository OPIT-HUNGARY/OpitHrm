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

namespace Opit\Notes\HolidayBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\HolidayBundle\Entity\LeaveSetting;
use Opit\Notes\HolidayBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of LeaveGroupFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage HolidayRateBundle
 */
class LeaveSettingsFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        // The ages are based on the hungarin law of 2014 year.
        $ages = array(
            0 => 20,
            25 => 21,
            28 => 22,
            31 => 23,
            33 => 24,
            35 => 25,
            37 => 26,
            39 => 27,
            41 => 28,
            43 => 29,
            45 => 30
        );

        foreach ($ages as $key => $value) {
            $leaveSetting = new LeaveSetting();
            $leaveSetting->setNumber($key);
            $leaveSetting->setNumberOfLeaves($value);
            $leaveSetting->setLeaveGroup($this->getReference('age'));
            $manager->persist($leaveSetting);
        }
        
        // The number of kids are based on the hungarin law of 2014 year.
        $kids = array(
            1 => 2,
            2 => 4,
            3 => 7
        );
        
        foreach ($kids as $key => $value) {
            $leaveSetting = new LeaveSetting();
            $leaveSetting->setNumber($key);
            $leaveSetting->setNumberOfLeaves($value);
            $leaveSetting->setLeaveGroup($this->getReference('children'));
            $manager->persist($leaveSetting);
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
