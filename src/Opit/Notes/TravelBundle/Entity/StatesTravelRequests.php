<?php

/*
 * This file is part of the Travel bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * This class is a container for the Travel Request Status model
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="notes_states_travel_requests")
 * @ORM\Entity()
 */
class StatesTravelRequests
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TravelRequest", inversedBy="states", fetch="EAGER")
     * @ORM\JoinColumn(name="travel_request_id", referencedColumnName="id")
     */
    protected $travelRequest;

     /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="travelRequests", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    public function __construct(\Opit\Notes\TravelBundle\Entity\Status $status = null)
    {
        $this->setStatus($status);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set TravelRequest
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @return StatesTravelRequests
     */
    public function setTravelRequest(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest = null)
    {
        $this->travelRequest = $travelRequest;

        return $this;
    }

    /**
     * Get TravelRequest
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelRequest
     */
    public function getTravelRequest()
    {
        return $this->TravelRequest;
    }

    /**
     * Set status
     *
     * @param \Opit\Notes\TravelBundle\Entity\Status $status
     * @return StatesTravelRequests
     */
    public function setStatus(\Opit\Notes\TravelBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\Notes\TravelBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }
}