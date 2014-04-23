<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Model;

/**
 * The TravelRequestUserInterface responsible for which behaviours have to
 * implement for the User entity to be able to work with the TravelRequest entity
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
interface TravelRequestUserInterface
{
    public function getEmployeeName();
    public function getEmail();
    public function getUsername();
}
