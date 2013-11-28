<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\UserBundle\Entity\User;

/**
 * Description of TravelRequestUser
 * Custom TravelRequestUser entity
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 * 
 * @ORM\Entity
 */
class TravelRequestUser extends User
{
    /**
     * @ORM\OneToMany(targetEntity="TravelRequest", mappedBy="user")
     */
    private $travelRequests;
    
    /**
     * @ORM\OneToMany(targetEntity="TravelRequest", mappedBy="teamManager")
     */
    private $travelRequestsTM;
    
    /**
     * @ORM\OneToMany(targetEntity="TravelRequest", mappedBy="generalManager")
     */
    private $travelRequestsGM;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $employeeName;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $groups;

    public function __construct()
    {
        parent::__construct();
        $this->travelRequests = new ArrayCollection();
        $this->travelRequestsTM = new ArrayCollection();
        $this->travelRequestsGM = new ArrayCollection();
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
     * Set username
     *
     * @param string $username
     * @return TravelRequestUser
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set employeeName
     *
     * @param string $employeeName
     * @return TravelRequestUser
     */
    public function setEmployeeName($employeeName)
    {
        $this->employeeName = $employeeName;
    
        return $this;
    }

    /**
     * Get employeeName
     *
     * @return string
     */
    public function getEmployeeName()
    {
        return $this->employeeName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return TravelRequestUser
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Add travelRequests
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequests
     * @return TravelRequestUser
     */
    public function addTravelRequest(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequests)
    {
        $this->travelRequests[] = $travelRequests;
    
        return $this;
    }

    /**
     * Remove travelRequests
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequests
     */
    public function removeTravelRequest(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequests)
    {
        $this->travelRequests->removeElement($travelRequests);
    }

    /**
     * Get travelRequests
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTravelRequests()
    {
        return $this->travelRequests;
    }

    /**
     * Add travelRequestsTM
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsTM
     * @return TravelRequestUser
     */
    public function addTravelRequestsTM(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsTM)
    {
        $this->travelRequestsTM[] = $travelRequestsTM;
    
        return $this;
    }

    /**
     * Remove travelRequestsTM
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsTM
     */
    public function removeTravelRequestsTM(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsTM)
    {
        $this->travelRequestsTM->removeElement($travelRequestsTM);
    }

    /**
     * Get travelRequestsTM
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTravelRequestsTM()
    {
        return $this->travelRequestsTM;
    }

    /**
     * Add travelRequestsGM
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsGM
     * @return TravelRequestUser
     */
    public function addTravelRequestsGM(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsGM)
    {
        $this->travelRequestsGM[] = $travelRequestsGM;
    
        return $this;
    }

    /**
     * Remove travelRequestsGM
     *
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsGM
     */
    public function removeTravelRequestsGM(\Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequestsGM)
    {
        $this->travelRequestsGM->removeElement($travelRequestsGM);
    }

    /**
     * Get travelRequestsGM
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTravelRequestsGM()
    {
        return $this->travelRequestsGM;
    }

    /**
     * Add groups
     *
     * @param \Opit\Notes\TravelBundle\Entity\Groups $groups
     * @return TravelRequestUser
     */
    public function addGroup(\Opit\Notes\UserBundle\Entity\Groups $groups)
    {
        $this->groups[] = $groups;
    
        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Opit\Notes\TravelBundle\Entity\Groups $groups
     */
    public function removeGroup(\Opit\Notes\UserBundle\Entity\Groups $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }
}