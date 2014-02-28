<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Opit\Notes\TravelBundle\Manager\EmailManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Description of UserService
 *
 * @author OPIT\Notes
 */
class UserService
{
    protected $mail;
    protected $encoder;
    protected $password;

    public function __construct(EmailManager $mail, EncoderFactoryInterface $encoder)
    {
        $this->mail = $mail;
        $this->encoder = $encoder;
        $this->password = '';
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
        $subject = 'Account for NOTES has been created';
        $template = 'newAccount';
        if ($isReset) {
            $subject = 'Password for NOTES has been reset';
            $template = 'passwordReset';
        }
        
        $this->mail->setRecipient($user->getEmail());
        $this->mail->setSubject($subject);
        
        $this->mail->setBaseTemplate(
            'OpitNotesUserBundle:Mail:' . $template . '.html.twig',
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
}
