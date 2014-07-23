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
use Opit\OpitHrm\StatusBundle\Entity\StatusWorkflow;
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Description of ApplicantStatusWorkflow
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\StatusBundle\Entity\StatusWorkflowRepository")
 */
class TravelExpenseStatusWorkflow extends StatusWorkflow
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\StatusBundle\Entity\Status", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\StatusBundle\Entity\Status", inversedBy="states")
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
     * @param Status $parent
     * @return StatusWorkflow
     */
    public function setParent(Status $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Status
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set status
     *
     * @param Status $status
     * @return StatusWorkflow
     */
    public function setStatus(Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return Status
     */
    public function getStatus()
    {
        return $this->status;
    }
}
