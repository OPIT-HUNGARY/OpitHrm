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
use Opit\OpitHrm\CoreBundle\Entity\AbstractComment;

/**
 * The child class used for applicant status comments. This class can be extended
 * with properties specific to applicant comments if needed.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
 *
 * @ORM\Table(name="opithrm_applicant_comments")
 * @ORM\Entity
 */
class CommentApplicantStatus extends AbstractComment
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $content;

    /**
     * @ORM\OneToOne(targetEntity="StatesApplicants", inversedBy="comment")
     * @ORM\JoinColumn(name="states_applicant_id", referencedColumnName="id")
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
     * Set content
     *
     * @param string $content
     * @return CommentTEStatus
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set status
     *
     * @param StatesApplicants
     * @return CommentApplicantStatus
     */
    public function setStatus(StatesApplicants $status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return StatesApplicants
     */
    public function getStatus()
    {
        return $this->status;
    }

}
