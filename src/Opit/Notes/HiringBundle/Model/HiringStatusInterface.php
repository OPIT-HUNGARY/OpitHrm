<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu> 
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Model;

/**
 * Description of HiringStatusInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
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
