<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\Email;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Opit\Component\Email\EmailManagerInterface;
use Opit\Component\Email\Exception\ConfigurationException;

/**
 * Description of EmailManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 */
class EmailManager implements EmailManagerInterface
{
    protected $swiftMailer;
    protected $templating;
    protected $logger;
    protected $config;

    private $subject;
    private $mailBody;
    private $recipient;
    private $emailFormat = 'text';
    private $attachment;

    /**
     * Constructor of email manager component.
     *
     * @param \Swift_Mailer                                    $swiftMailer
     * @param \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine $templating
     * @param \Psr\Log\LoggerInterface                         $logger
     * @param array                                            $config
     */
    public function __construct(\Swift_Mailer $swiftMailer, TwigEngine $templating, LoggerInterface $logger = null, array $config = array())
    {
        $this->swiftMailer = $swiftMailer;
        $this->templating = $templating;
        $this->logger = $logger;
        $this->config = $config;
        $this->attachment = null;

        $this->validateConfig();
    }

    /**
     * Method to validate if mail_sender is in config.
     *
     * @throws Exception\ConfigurationException
     */
    protected function validateConfig()
    {
        if (!array_key_exists('mail_sender', $this->config)) {
            throw new ConfigurationException('No mail sender found in config.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * {@inheritdoc}
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
    }

    /**
     * Method to set body template for mail.
     *
     * @param string $template
     * @param array  $templateVars
     */
    public function setBodyByTemplate($template, array $templateVars = array())
    {
        if (strpos($template, '.html.twig')) {
            $this->emailFormat = 'text/html';
        }
        $this->mailBody = $this->templating->render($template, array('templateVars' => $templateVars));

        if (null !== $this->logger) {
            $this->logger->info('[EmailManager] Mail body set by template.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(array $attachment, $dynamic = false)
    {
        // Adding existing file or generated content.
        if (isset($attachment['path'])) {
            // add existing file
            $this->attachment = \Swift_Attachment::fromPath($attachment['path']);

            // set filename
            if (isset($attachment['filename'])) {
                $this->attachment->setFilename($attachment['filename']);
            }
        } elseif (true === $dynamic && isset($attachment['content'])) {
            // add dynamic content
            $this->attachment = \Swift_Attachment::newInstance($attachment['content']);

            // set content-type
            if (isset($attachment['type'])) {
                $this->attachment->setContentType($attachment['type']);
            }
            // set filename
            if (isset($attachment['filename'])) {
                $this->attachment->setFilename($attachment['filename']);
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function sendMail($content = null)
    {
        if (null !== $content) {
            $this->mailBody = $content;
        }
        if (!$this->mailBody) {
            throw new \RuntimeException('A mail body must be present!');
        }

        // create email to send, render template for email
        $message = \Swift_Message::newInstance()
            ->setSubject($this->subject)
            ->setFrom($this->config['mail_sender'])
            ->setTo($this->recipient)
            ->setBody($this->mailBody, $this->emailFormat);

        // add attachment
        if (null !== $this->attachment) {
            $message->attach($this->attachment);
        }
        // send the message
        $result = $this->swiftMailer->send($message);

        if (null !== $this->logger) {
            $this->logger->info('[EmailManager] Email sent.');
        }

        return $result;
    }
}
