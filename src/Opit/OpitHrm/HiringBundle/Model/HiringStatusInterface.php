<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu> 
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Model;

/**
 * Description of HiringStatusInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
interface HiringStatusInterface
{
    const SCHEDULE_WRITTEN_EXAM = 7;
    const WRITTEN_EXAM_PASSED = 8;
    const WRITTEN_EXAM_FAILED = 9;
    const SCHEDULE_INTERVIEW = 10;
    const INTERVIEW_PASSED = 11;
    const INTERVIEW_FAILED = 12;
    const HIRED = 13;
}
