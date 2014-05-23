<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Component\Email;

/**
 * Description of EmailManagerInterface
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */
interface EmailManagerInterface
{
    /**
     * Method to set mail subject.
     * 
     * @param string $subject
     */
    public function setSubject($subject);
    
    /**
     * Method to set mail recipient.
     * 
     * @param string $recipient
     */
    public function setRecipient($recipient);

    /**
     * Sends an email with given parameters
     * Content can be explicitly set and overwrites base template settings
     * 
     * @param string $content
     */
    public function sendMail($content = null);
}
