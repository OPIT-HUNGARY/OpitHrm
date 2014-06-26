<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\StatusBundle\Entity\Status;
use Opit\Notes\CoreBundle\Entity\AbstractBase;
use Opit\Notes\HiringBundle\Entity\Applicant;

/**
 * This class is a container for the Applicant Status model
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage HiringBundle
 *
 * @ORM\Table(name="notes_states_applicants")
  * @ORM\Entity(repositoryClass="Opit\Notes\HiringBundle\Entity\StatesApplicantsRepository")
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
     * @ORM\ManyToOne(targetEntity="\Opit\Notes\StatusBundle\Entity\Status", fetch="EAGER")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     */
    protected $status;

    /**
     * @ORM\OneToOne(targetEntity="\Opit\Notes\HiringBundle\Entity\CommentApplicantStatus", mappedBy="status", cascade={"persist", "remove"})
     */
    protected $comment;

    /**
     * 
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @param \Opit\Notes\HiringBundle\Entity\Applicant $applicant
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
     * @param \Opit\Notes\StatusBundle\Entity\Status $status
     * @return StatesLeaveRequest
     */
    public function setStatus(\Opit\Notes\StatusBundle\Entity\Status $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \Opit\Notes\StatusBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment
     *
     * @param \Opit\Notes\LeaveBundle\Entity\CommentLeaveStatus $comment
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
     * @return \Opit\Notes\LeaveBundle\Entity\CommentLeaveStatus
     */
    public function getComment()
    {
        return $this->comment;
    }

}
