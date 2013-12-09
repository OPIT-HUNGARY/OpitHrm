<?php
namespace Opit\Notes\TravelBundle\Model;

/**
 * The TravelRequestUserInterface responsible for which behaviours have to
 * implement for the User entity to be able to work with the TravelRequest entity
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
interface TravelRequestUserInterface
{
    public function getEmployeeName();
    public function getEmail();
    public function getUsername();
}
