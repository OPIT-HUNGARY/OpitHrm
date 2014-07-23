<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\CoreBundle\Entity\AbstractBase;
use Opit\OpitHrm\HiringBundle\Entity\Applicant;

/**
 * This class is a container for the Applicant Status model
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 *
 * @ORM\Table(name="opithrm_states_applicants")
  * @ORM\Entity(repositoryClass="Opit\OpitHrm\HiringBundle\Entity\StatesApplicantsRepository")
 */
class StatesApplicants extends AbstractBase
{

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Applicant", inversedBy="states", fetch="EAGER")
     * @ORM\JoinColumn(name="applicant_id", referencedColumnName="id")
     */
    protected $applicant;

    /**
     * @ORM\ManyToOne(targetEntity="\Opit\OpitHrm\StatusBundle\Entity\Status", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="\Opit\OpitHrm\HiringBundle\Entity\CommentApplicantStatus", mappedBy="status", cascade={"persist", "remove"})
     */
    protected $comment;

    /**
     * 
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     * @param \Opit\OpitHrm\HiringBundle\Entity\Applicant $applicant
     */
    public function __construct(Status $status = null, Applicant $applicant = null)
    {
        parent::__construct();
        $this->setStatus($status);
        $this->setApplicant($applicant);
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
     * Set applicant
     *
     * @param Applicant $applicant
     * @return StatesApplicants
     */
    public function setApplicant(Applicant $applicant = null)
    {
        $this->applicant = $applicant;

        return $this;
    }

    /**
     * Get applicant
     *
     * @return Applicant
     */
    public function getApplicant()
    {
        return $this->applicant;
    }

    /**
     * Set status
     *
     * @param \Opit\OpitHrm\StatusBundle\Entity\Status $status
     * @return StatesLeaveRequest
     */
    public function setStatus(\Opit\OpitHrm\StatusBundle\Entity\Status $status = null)
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
     * @param \Opit\OpitHrm\LeaveBundle\Entity\CommentLeaveStatus $comment
     * @return StatesLeaveRequests
     */
    public function setComment(CommentApplicantStatus $comment = null)
    {
        $comment->setStatus($this);
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \Opit\OpitHrm\LeaveBundle\Entity\CommentLeaveStatus
     */
    public function getComment()
    {
        return $this->comment;
    }

}
