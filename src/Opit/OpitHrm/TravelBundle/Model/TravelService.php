<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Model;

use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;
use Opit\OpitHrm\TravelBundle\Manager\TravelNotificationManager;
use Symfony\Component\Routing\Router;
use Opit\Component\Email\EmailManagerInterface;
use Opit\OpitHrm\CurrencyRateBundle\Model\ExchangeRateInterface;
use Opit\Component\Utils\Utils;
use Opit\OpitHrm\TravelBundle\Entity\Token;


/**
 * Description of TravelService
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
abstract class TravelService
{
    protected $statusManager;
    protected $travelNotification;
    protected $router;
    protected $mailer;
    protected $exchangeService;
    protected $options;
    protected $factory;

    public function setTravelNotificationManager(TravelNotificationManager $travelNotificationManager)
    {
        $this->travelNotificationManager = $travelNotificationManager;
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function setEmailManager(EmailManagerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function setExchangeService(ExchangeRateInterface $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    public function setFactory($factory)
    {
        $this->factory = $factory;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * Composes and send an email based on a status change
     *
     * @param Status  $status
     * @param mixed   $resource
     */
    public function prepareEmail(Status $status, $resource)
    {
        $applicationName = $this->options['applicationName'];
        // get template name by converting entity name first letter to lower
        $className = Utils::getClassBasename($resource);
        // lowercase first character of string
        $template = lcfirst($className);
        $statusName = $status->getName();
        $statusId = $status->getId();
        // split class name at uppercase letters
        $subjectType = preg_split('/(?=[A-Z])/', $className);
        // decide if resource is request or expense, if is expense get its request
        $travelRequest = ($resource instanceof TravelExpense) ? $resource->getTravelRequest() : $resource;
        $generalManager = $travelRequest->getGeneralManager();
        // call method located in travel expense service
        $estimatedCosts = $this->getTRCosts($travelRequest);
        // create string for email travel type e.g.(Travel expense, Travel request)
        $subjectTravelType = $subjectType[1] . ' ' . strtolower($subjectType[2]);
        $stateChangeLinks = array();

        if (Status::FOR_APPROVAL === $statusId) {
            $travelToken = $this->setTravelToken($resource->getId());

            // Exclude current status from next states to prepare the email
            $nextStates = $this->statusManager->getNextStates($status);
            unset($nextStates[$statusId]);

            foreach ($nextStates as $key => $value) {
                // Generate links that can be used to change the status of the travel request
                $stateChangeLinks[] = $this->router->generate('OpitOpitHrmTravelBundle_change_status', array(
                    'gmId' => $generalManager->getId(),
                    'travelType' => $resource::getType(),
                    'status' => $key,
                    'token' => $travelToken
                ), true);
            }

            $recipient = $generalManager->getEmail();
            $templateVariables = array(
                'nextStates' => $nextStates,
                'stateChangeLinks' => $stateChangeLinks
            );
        } else {
            $recipient = $travelRequest->getUser()->getEmail();
            $templateVariables = array(
                'currentState' => $statusName,
                'url' => $this->router->generate('OpitOpitHrmUserBundle_security_login', array(), true)
            );

            switch ($statusId) {
                case Status::APPROVED:
                    $templateVariables['isApproved'] = true;
                    break;
                case Status::REVISE:
                    $templateVariables['isRevised'] = true;
                    break;
                case Status::REJECTED:
                    $templateVariables['isRejected'] = true;
                    break;
                case Status::CREATED:
                    $templateVariables['isCreated'] = true;
                    break;
            }
        }

        // set estimated in HUF and EUR for template
        $templateVariables['estimatedCostsEUR'] = ceil($estimatedCosts['EUR']);
        $templateVariables['estimatedCostsHUF'] = ceil($estimatedCosts['HUF']);
        $templateVariables[$template] = $resource;

        $this->mailer->setRecipient($recipient);
        $this->mailer->setSubject(
           '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM').'] - ' .$subjectTravelType
            . ' status changed - ' . $statusName .' ('. $travelRequest->getTravelRequestId().')'
        );

        $this->mailer->setBodyByTemplate(
            'OpitOpitHrmTravelBundle:Mail:' . $template . '.html.twig',
            $templateVariables
        );

        $this->mailer->sendMail();
    }

    /**
     * Get the travel request costs
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return array Travel request costs in HUF and EUR
     */
    public function getTRCosts(TravelRequest $travelRequest)
    {
        $approvedCostsEUR = 0;
        $approvedCostsHUF = 0;
        $midRate = $this->getConversionDate($travelRequest);
        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $accomodationCost = $accomodation->getCost();
            $accomodationCurrency = $accomodation->getCurrency();

            $approvedCostsHUF += $this->exchangeService->convertCurrency(
                $accomodationCurrency->getCode(),
                'HUF',
                $accomodationCost,
                $midRate
            );
            $approvedCostsEUR += $this->exchangeService->convertCurrency(
                $accomodationCurrency->getCode(),
                'EUR',
                $accomodationCost,
                $midRate
            );
        }

        foreach ($travelRequest->getDestinations() as $destination) {
            $destinationCost = $destination->getCost();
            $destinationCurrency = $destination->getCurrency();

            $approvedCostsHUF += $this->exchangeService->convertCurrency(
                $destinationCurrency->getCode(),
                'HUF',
                $destinationCost,
                $midRate
            );
            $approvedCostsEUR += $this->exchangeService->convertCurrency(
                $destinationCurrency->getCode(),
                'EUR',
                $destinationCost,
                $midRate
            );
        }

        return array('HUF' => $approvedCostsHUF, 'EUR' => $approvedCostsEUR);
    }

    /**
     * Method to set token for a travel request or travel expense.
     *
     * @param integer $id
     * @return string $travelToken
     */
    public function setTravelToken($id)
    {
        //set token for travel
        $token = new Token();
        // encode token with factory encoder
        $encoder = $this->factory->getEncoder($token);
        $travelToken =
            str_replace('/', '', $encoder->encodePassword(serialize($id) . date('Y-m-d H:i:s'), ''));
        $token->setToken($travelToken);
        $token->setTravelId($id);
        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $travelToken;
    }

    /**
     * Get conversion date
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest|\Opit\OpitHrm\TravelBundle\Entity\TravelExpense $resource travel request or travel expense object.
     */
    abstract public function getConversionDate($resource);
}
