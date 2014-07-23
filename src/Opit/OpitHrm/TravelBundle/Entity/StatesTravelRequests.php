<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\CoreBundle\Entity\AbstractBase;
use Opit\OpitHrm\TravelBundle\Entity\TravelRequest;

/**
 * This class is a container for the Travel Request Status model
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="opithrm_states_travel_requests")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\TravelBundle\Entity\StatesTravelRequestsRepository")
 */
class StatesTravelRequests extends AbstractBase
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
     * @ORM\ManyToOne(targetEntity="Opit\OpitHrm\TravelBundle\Model\TravelStatusInterface", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="CommentTRStatus", mappedBy="status", cascade={"persist", "remove"})
     */
    protected $comment;

    public function __construct(Status $status = null, TravelRequest $travelRequest = null)
    {
        parent::__construct();
        $this->setStatus($status);
        $this->setTravelRequest($travelRequest);
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
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @return StatesTravelRequests
     */
    public function setTravelRequest(TravelRequest $travelRequest = null)
    {
        $this->travelRequest = $travelRequest;

        return $this;
    }

    /**
     * Get TravelRequest
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\TravelRequest
     */
    public function getTravelRequest()
    {
        return $this->travelRequest;
    }

    /**
     * Set status
     *
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     * @return StatesTravelRequests
     */
    public function setStatus(Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\OpitHrm\StatusBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\CommentTRStatus $comment
     * @return StatesTravelRequests
     */
    public function setComment(\Opit\OpitHrm\TravelBundle\Entity\CommentTRStatus $comment = null)
    {
        $comment->setStatus($this);
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \Opit\OpitHrm\TravelBundle\Entity\CommentTRStatus
     */
    public function getComment()
    {
        return $this->comment;
    }
}
