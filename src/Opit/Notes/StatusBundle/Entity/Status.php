<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\StatusBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\StatusBundle\Entity\StatusWorkflow;

/**
 * This class is a container for the Status model
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage StatusBundle
 *
 * @ORM\Table(name="notes_status")
 * @ORM\Entity(repositoryClass="Opit\Notes\StatusBundle\Entity\StatusRepository")
 */
class Status
{
    const CREATED = 1;
    const FOR_APPROVAL = 2;
    const REVISE = 3;
    const APPROVED = 4;
    const REJECTED = 5;
    const PAID = 6;
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    protected $name;

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
        $this->children = new ArrayCollection();
        $this->states = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
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
     * @param StatusWorkflow $children
     * @return Status
     */
    public function addChildren(StatusWorkflow $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param StatusWorkflow $children
     */
    public function removeChildren(StatusWorkflow $children)
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
     * @param StatusWorkflow $states
     * @return Status
     */
    public function addState(StatusWorkflow $states)
    {
        $this->states[] = $states;

        return $this;
    }

    /**
     * Remove states
     *
     * @param StatusWorkflow $states
     */
    public function removeState(StatusWorkflow $states)
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
     * Add children
     *
     * @param StatusWorkflow $children
     * @return Status
     */
    public function addChild(StatusWorkflow $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param StatusWorkflow $children
     */
    public function removeChild(StatusWorkflow $children)
    {
        $this->children->removeElement($children);
    }
}
