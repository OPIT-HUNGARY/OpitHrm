<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of AbstractComment
 *
 * This is the parent abstract class for the comment model.
 * It is defined as abstract to support mutiple inheritance.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CoreBundle
 *
 * @ORM\Table(name="opithrm_comments")
 * @ORM\Entity
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     "tr" = "Opit\OpitHrm\TravelBundle\Entity\CommentTRStatus",
 *     "te" = "Opit\OpitHrm\TravelBundle\Entity\CommentTEStatus",
 *     "leave" = "Opit\OpitHrm\LeaveBundle\Entity\CommentLeaveStatus",
 *     "applicant" = "Opit\OpitHrm\HiringBundle\Entity\CommentApplicantStatus"
 * })
 */
abstract class AbstractComment extends AbstractBase
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

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
     * @return AbstractComment
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
}
