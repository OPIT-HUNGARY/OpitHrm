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
 * This class is a container for the travel status workflow.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 *
 * @ORM\Table(name="notes_status_workflow")
 * @ORM\Entity()
 */
class StatusWorkflow
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="states")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

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
     * Set parent
     *
     * @param \Opit\Notes\TravelBundle\Entity\Status $parent
     * @return StatusWorkflow
     */
    public function setParent(\Opit\Notes\TravelBundle\Entity\Status $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \Opit\Notes\TravelBundle\Entity\Status
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set status
     *
     * @param \Opit\Notes\TravelBundle\Entity\Status $status
     * @return StatusWorkflow
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