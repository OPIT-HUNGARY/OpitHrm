<?php

namespace Opit\Notes\TravelBundle\Model;

/**
 * Travel Resource Interface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
interface TravelResourceInterface
{
    public function getId();
    public function getUser();
    public function setUser(\Opit\Notes\TravelBundle\Model\TravelRequestUserInterface $user = null);
}
