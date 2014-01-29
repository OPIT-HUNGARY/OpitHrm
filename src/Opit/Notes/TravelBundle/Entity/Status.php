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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * This class is a container for the Status model
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="notes_status")
 * @ORM\Entity(repositoryClass="Opit\Notes\TravelBundle\Entity\StatusRepository")
 */
class Status
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="StatesTravelRequests", mappedBy="status")
     */
    protected $travelRequests;

    /**
     * @ORM\OneToMany(targetEntity="StatesTravelExpenses", mappedBy="status")
     */
    protected $travelExpenses;

     /**
     * @ORM\OneToMany(targetEntity="StatusWorkflow", mappedBy="parent")
     **/
    protected $children;

    /**
     * @ORM\OneToMany(targetEntity="StatusWorkflow", mappedBy="status")
     **/
    protected $states;

    public function __construct()
    {
        $this->travelRequests = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->states = new ArrayCollection();
        $this->travelExpenses = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Status
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add children
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatusWorkflow $children
     * @return Status
     */
    public function addChildren(\Opit\Notes\TravelBundle\Entity\StatusWorkflow $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatusWorkflow $children
     */
    public function removeChildren(\Opit\Notes\TravelBundle\Entity\StatusWorkflow $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add states
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatusWorkflow $states
     * @return Status
     */
    public function addState(\Opit\Notes\TravelBundle\Entity\StatusWorkflow $states)
    {
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatusWorkflow $states
     */
    public function removeState(\Opit\Notes\TravelBundle\Entity\StatusWorkflow $states)
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
     * Add travelRequests
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelRequests $travelRequests
     * @return Status
     */
    public function addTravelRequest(\Opit\Notes\TravelBundle\Entity\StatesTravelRequests $travelRequests)
    {
        $travelRequests->setStatus($this); // synchronously updating inverse side
        $this->travelRequests[] = $travelRequests;

        return $this;
    }

    /**
     * Remove travelRequests
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelRequests $travelRequests
     */
    public function removeTravelRequest(\Opit\Notes\TravelBundle\Entity\StatesTravelRequests $travelRequests)
    {
        $this->travelRequests->removeElement($travelRequests);
    }

    /**
     * Add children
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatusWorkflow $children
     * @return Status
     */
    public function addChild(\Opit\Notes\TravelBundle\Entity\StatusWorkflow $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatusWorkflow $children
     */
    public function removeChild(\Opit\Notes\TravelBundle\Entity\StatusWorkflow $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Add travel expense
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $travelExpenses
     * @return Status
     */
    public function addTravelExpense(\Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $travelExpenses)
    {
        $travelExpenses->setStatus($this); // synchronously updating inverse side
        $this->travelExpenses[] = $travelExpenses;

        return $this;
    }

    /**
     * Remove travelExpenses
     *
     * @param \Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $travelExpenses
     */
    public function removeTravelExpense(\Opit\Notes\TravelBundle\Entity\StatesTravelExpenses $travelExpenses)
    {
        $this->travelExpenses->removeElement($travelExpenses);
    }

    /**
     * Get travelExpenses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTravelExpenses()
    {
        return $this->travelExpenses;
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
}