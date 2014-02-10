<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\StatesTravelRequests;

/**
 * travel_request
 *
 * @ORM\Table(name="notes_travel_request")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\TravelRequestRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class TravelRequest
{
    private $trIdPattern = 'TR-{year}-{id}';
    
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\OneToMany(targetEntity="TRNotification", mappedBy="travelRequest", cascade={"persist", "remove"})
     */
    private $id;

    /**
      * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelRequestUserInterface", inversedBy="userTravelRequests")
      * @Assert\NotBlank(message="Employee name cannot be empty.")
      * @var TravelRequestUserInterface
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="departure_date", type="date")
     * @Assert\NotBlank(message="Departure date cannot be empty.")
     * @Assert\Date()
     */
    private $departureDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="arrival_date", type="date")
     * @Assert\NotBlank(message="Arrival date cannot be empty.")
     * @Assert\Date()
     */
    private $arrivalDate;

    /**
     * @var string
     *
     * @ORM\Column(name="trip_purpose", type="string", length=255)
     * @Assert\NotBlank(message="Trip purpose cannot be empty.")
     */
    private $tripPurpose;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelRequestUserInterface", inversedBy="tmTravelRequests")
     * @var TravelRequestUserInterface
     */
    private $teamManager;

    /**
     * @ORM\ManyToOne(targetEntity="Opit\Notes\TravelBundle\Model\TravelRequestUserInterface", inversedBy="gmTravelRequests")
     * @Assert\NotBlank(message="General manager cannot be empty.")
     * @var TravelRequestUserInterface
     */
    private $generalManager;

    /**
     * @var boolean
     *
     * @ORM\Column(name="customer_related", type="boolean")
     */
    private $customerRelated;

    /**
     * @var string
     * @ORM\Column(name="opportunity_name", type="string", nullable=true)
     */
    private $opportunityName;
    
    /**
     * @ORM\OneToMany(targetEntity="TRDestination", mappedBy="travelRequest", cascade={"persist", "remove"})
     * @Assert\NotBlank(message="Destinations cannot be empty.")
     */
    private $destinations;
    
    /**
     * @ORM\OneToMany(targetEntity="TRAccomodation", mappedBy="travelRequest", cascade={"persist", "remove"})
     * @Assert\NotBlank(message="Accomodations date cannot be empty.")
     */
    private $accomodations;
    
    /**
     * @var text
     * @ORM\Column(name="travel_request_id", type="string", length=11, nullable=true)
     */
    private $travelRequestId;
    
    /**
     * @var TravelExpense
     * @ORM\OneToOne(targetEntity="TravelExpense", mappedBy="travelRequest", cascade={"persist", "remove"})
     */
    private $travelExpense;
    
    /**
     * @ORM\OneToMany(targetEntity="StatesTravelRequests", mappedBy="travelRequest", cascade={"persist", "remove"})
     */
    protected $states;

    public function __construct()
    {
        $this->destinations = new ArrayCollection();
        $this->accomodations = new ArrayCollection();
        $this->states = new ArrayCollection();
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
     * Set departureDate
     *
     * @param \DateTime $departureDate
     * @return travel_request
     */
    public function setDepartureDate($departureDate)
    {
        $this->departureDate = $departureDate;
    
        return $this;
    }

    /**
     * Get departureDate
     *
     * @return \DateTime
     */
    public function getDepartureDate()
    {
        return $this->departureDate;
    }

    /**
     * Set arrivalDate
     *
     * @param \DateTime $arrivalDate
     * @return travel_request
     */
    public function setArrivalDate($arrivalDate)
    {
        $this->arrivalDate = $arrivalDate;
    
        return $this;
    }

    /**
     * Get arrivalDate
     *
     * @return \DateTime
     */
    public function getArrivalDate()
    {
        return $this->arrivalDate;
    }

    /**
     * Set tripPurpose
     *
     * @param string $tripPurpose
     * @return travel_request
     */
    public function setTripPurpose($tripPurpose)
    {
        $this->tripPurpose = $tripPurpose;
    
        return $this;
    }

    /**
     * Get tripPurpose
     *
     * @return string
     */
    public function getTripPurpose()
    {
        return $this->tripPurpose;
    }

    /**
     * Set customerRelated
     *
     * @param boolean $customerRelated
     * @return travel_request
     */
    public function setCustomerRelated($customerRelated)
    {
        $this->customerRelated = $customerRelated;
    
        return $this;
    }

    /**
     * Get customerRelated
     *
     * @return boolean
     */
    public function getCustomerRelated()
    {
        return $this->customerRelated;
    }

    /**
     * Set opportunityName
     *
     * @param string $opportunityName
     * @return travel_request
     */
    public function setOpportunityName($opportunityName)
    {
        $this->opportunityName = $opportunityName;
    
        return $this;
    }

    /**
     * Get opportunityName
     *
     * @return string
     */
    public function getOpportunityName()
    {
        return $this->opportunityName;
    }

    /**
     * Add destinations
     *
     * @param \Opit\Notes\TravelBundle\Entity\TRDestination $destinations
     * @return TravelRequest
     */
    public function addDestination(\Opit\Notes\TravelBundle\Entity\TRDestination $destinations)
    {
        $this->destinations[] = $destinations;
        $destinations->setTravelRequest($this); // synchronously updating inverse side
    
        return $this;
    }

    /**
     * Remove destinations
     *
     * @param \Opit\Notes\TravelBundle\Entity\TRDestination $destinations
     */
    public function removeDestination(\Opit\Notes\TravelBundle\Entity\TRDestination $destinations)
    {
        $this->destinations->removeElement($destinations);
    }

    /**
     * Get destinations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinations()
    {
        return $this->destinations;
    }

    /**
     * Add accomodations
     *
     * @param \Opit\Notes\TravelBundle\Entity\TRAccomodation $accomodations
     * @return TravelRequest
     */
    public function addAccomodation(\Opit\Notes\TravelBundle\Entity\TRAccomodation $accomodations)
    {
        $this->accomodations[] = $accomodations;
        $accomodations->setTravelRequest($this); // synchronously updating inverse side
    
        return $this;
    }

    /**
     * Remove accomodations
     *
     * @param \Opit\Notes\TravelBundle\Entity\TRAccomodation $accomodations
     */
    public function removeAccomodation(\Opit\Notes\TravelBundle\Entity\TRAccomodation $accomodations)
    {
        $this->accomodations->removeElement($accomodations);
    }

    /**
     * Get accomodations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccomodations()
    {
        return $this->accomodations;
    }

    /**
     * Set user
     *
     * @param \Opit\Notes\UserBundle\Entity\User $user
     * @return TravelRequest
     */
    public function setUser(\Opit\Notes\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Opit\Notes\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set teamManager
     *
     * @param \Opit\Notes\UserBundle\Entity\User $teamManager
     * @return TravelRequest
     */
    public function setTeamManager(\Opit\Notes\UserBundle\Entity\User $teamManager = null)
    {
        $this->teamManager = $teamManager;
    
        return $this;
    }

    /**
     * Get teamManager
     *
     * @return \Opit\Notes\TravelBundle\Entity\User
     */
    public function getTeamManager()
    {
        return $this->teamManager;
    }

    /**
     * Set generalManager
     *
     * @param \Opit\Notes\UserBundle\Entity\User $generalManager
     * @return TravelRequest
     */
    public function setGeneralManager(\Opit\Notes\UserBundle\Entity\User $generalManager = null)
    {
        $this->generalManager = $generalManager;
    
        return $this;
    }

    /**
     * Get generalManager
     *
     * @return \Opit\Notes\UserBundle\Entity\User
     */
    public function getGeneralManager()
    {
        return $this->generalManager;
    }


    /**
     * Set travelRequestId
     
     * @ORM\PostPersist
     * @param string $travelRequestId
     * @return TravelRequest
     */
    public function setTravelRequestId($travelRequestId = null)
    {
        $this->travelRequestId = $travelRequestId;
        #update travel request id on post persist event
        if (null === $this->travelRequestId) {
            $this->travelRequestId = str_replace(
                array('{year}', '{id}'),
                array(date('y'), sprintf('%05d', $this->id)),
                $this->trIdPattern
            );
        }
        
        return $this;
    }

    /**
     * Get travelRequestId
     *
     * @return string 
     */
    public function getTravelRequestId()
    {
        return $this->travelRequestId;
    }

    /**
     * Set travelExpense
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @return TravelRequest
     */
    public function setTravelExpense(TravelExpense $travelExpense = null)
    {
        $this->travelExpense = $travelExpense;
    
        return $this;
    }

    /**
     * Get travelExpense
     *
     * @return \Opit\Notes\TravelBundle\Entity\TravelExpense
     */
    public function getTravelExpense()
    {
        return $this->travelExpense;
    }

    /**
     * Add states
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelRequests $states
     * @return TravelRequest
     */
    public function addState(StatesTravelRequests $states)
    {
        $states->setTravelRequest($this); // synchronously updating inverse side
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelRequests $states
     */
    public function removeState(StatesTravelRequests $states)
    {
        $this->states->removeElement($states);
    }

    /**
     * Get states
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStates()
    {
        return $this->states;
    }
    
    /**
     * 
     * @param Entity $notifications
     * @return \Opit\Notes\TravelBundle\Entity\TravelRequest
     */
    public function setNotifications($notifications)
    {
        $this->notifications = $notifications;
        
        return $this;
    }
    
    /**
     * 
     * @return Entitiy
     */
    public function getNotification()
    {
        return $this->notifications;
    }
}