<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Manager;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class EmailManager
{
    protected $mailer;
    protected $template;
    protected $securityContext;
    protected $config;
    
    protected $subject;
    protected $baseTemplate;
    protected $recipient;
    protected $emailFormat = 'text';
    protected $from;
    
    public function __construct($mailer, $template, $securityContext, array $config)
    {
        $this->mailer = $mailer;
        $this->template = $template;
        $this->securityContext = $securityContext;
        $this->config = $config;
    }
    
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }
    
    public function setBaseTemplate($template, $templateVars = array())
    {
        if (strpos($template, '.html.twig')) {
            $this->emailFormat = 'text/html';
        }
        $this->baseTemplate = $this->template->render(
            $template,
            array('templateVars' => $templateVars)
        );
    }
    
    public function sendMail() {
        $user = $this->securityContext->getToken()->getUser();
        if (!$this->baseTemplate) {
            $this->setBaseTemplate('OpitNotesTravelBundle:Mail:default.txt.twig', array('user' => $user));
        }
        
        // create email to send, render template for email
        $message = \Swift_Message::newInstance()
            ->setSubject($this->subject)
            ->setFrom($this->config['mail_sender'])
            ->setTo($this->recipient)
            ->setBody($this->baseTemplate, $this->emailFormat);
        
        // send the message
        $this->mailer->send($message);
    }
}
