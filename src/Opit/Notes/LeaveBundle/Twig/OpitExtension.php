<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Twig;

use Opit\Component\Utils\Utils;

/**
 * Twig CoreExtension class
 *
 * @author OPIT Consulting Kft. - NOTES/TAO Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class OpitExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'getLRLastNoticeDate' => new \Twig_Function_Method($this, 'getLRLastNoticeDate')
        );
    }
    
    public function getLRLastNoticeDate($lrCreated)
    {
        $lastContactDate = clone $lrCreated;
        date_add($lastContactDate, date_interval_create_from_date_string('5 days'));
        
        $weekendDays = Utils::countWeekendDays($lrCreated->getTimeStamp(), $lastContactDate->getTimeStamp());
        date_add($lastContactDate, date_interval_create_from_date_string($weekendDays . ' days'));
        
        return $lastContactDate->format('Y-m-d');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'opit_leave_bundle_extension';
    }
}
