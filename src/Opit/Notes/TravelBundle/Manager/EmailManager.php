<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Manager;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
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
        if (!$this->baseTemplate) {
            $user = $this->securityContext->getToken()->getUser();
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
