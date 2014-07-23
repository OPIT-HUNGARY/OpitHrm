<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Opit\Component\Email\EmailManagerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Description of UserService
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class UserService
{
    protected $mail;
    protected $encoder;
    protected $password;
    protected $systemRoles;
    protected $options;

    public function __construct(EmailManagerInterface $mail, EncoderFactoryInterface $encoder, $systemRoles = array())
    {
        $this->mail = $mail;
        $this->encoder = $encoder;
        $this->password = '';
        $this->systemRoles = $systemRoles;
        $this->options['applicationName'];
    }

    /**
     * Method to send a mail to the user that his account has been created
     * or that his password has been reset.
     *
     * @param UserInterface $user
     * @param boolean $isReset
     */
    public function sendNewPasswordMail(UserInterface $user, $isReset = false)
    {
        $applicationName = $this->options['applicationName'];
        $subject = '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - New account created';
        $template = 'newAccount';

        if ($isReset) {
            $subject = '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - Password reset';
            $template = 'passwordReset';
        }

        $this->mail->setRecipient($user->getEmail());
        $this->mail->setSubject($subject);

        $this->mail->setBodyByTemplate(
            'OpitOpitHrmUserBundle:Mail:' . $template . '.html.twig',
            array('password' => $this->password, 'user' => $user)
        );

        $this->mail->sendMail();
    }

    /**
     * Method to generate a random string.
     *
     * @param integer $length
     * @return string
     */
    public function generatePassword($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        for ($i = 0; $i < $length; $i++) {
            $this->password .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $this->password;
    }

    /**
     * Method to encode password.
     *
     * @param UserInterface $user
     * @param string $password
     * @return string
     */
    public function encodePassword(UserInterface $user, $password = null)
    {
        if (null === $password) {
            $this->generatePassword();
        }

        return $this->encoder->getEncoder($user)->encodePassword($this->password, $user->getSalt());
    }

    /**
     * Returns all authorized roles of a user
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return array
     */
    public function getInheritedRoles(UserInterface $user)
    {
        $availableRoles = array();
        $userRoles = $user->getRoles();

        foreach($userRoles as $userRole) {
            $availableRoles[] = $userRole->getRole();
            if (array_key_exists($userRole->getRole(), $this->systemRoles)) {
                $availableRoles = array_merge($availableRoles, $this->systemRoles[$userRole->getRole()]);
            }
        }

        $roles = array_values(
            array_unique($availableRoles)
        );

        return $roles;
    }
}
