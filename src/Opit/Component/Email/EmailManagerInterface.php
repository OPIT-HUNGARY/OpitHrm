<?php

/*
 *  This file is part of the OPIT-HRM project.
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
 * @package OPIT-HRM
 * @subpackage Component
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
     * Method to add additional mail recipients.
     *
     * @param string $email An email address
     * @param string $name The recipient's name (optional)
     */
    public function addRecipient($email, $name = null);

    /**
     * Sends an email with given parameters
     * Content can be explicitly set and overwrites base template settings
     *
     * @param string $content
     */
    public function sendMail($content = null);

    /**
     * Method to add attachment.
     * It be able to handles existing files and dynamic contents
     * Dynamic contents those files are generated at runtime, such as PDF documents or images
     * can be attached directly to a message without writing them out to disk.
     *
     * @param array $attachment
     * @param boolean $dynamic flag to sign if the attachment is generated at runtime.
     */
    public function addAttachment(array $attachment, $dynamic = false);
}
