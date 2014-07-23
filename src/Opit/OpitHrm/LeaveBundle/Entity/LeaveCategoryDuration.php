<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leave category duration
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 *
 * @ORM\Table(name="opithrm_leave_category_duration")
 * @ORM\Entity()
 */
class LeaveCategoryDuration
{
    const FULLDAY = 1;
    const HALFDAY = 2;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="leave__category_duration_name", type="string", length=255)
     */
    protected $leaveCategoryDurationName;

    /**
     * Set id
     *
     * @param integer $id
     * @return LeaveCategoryDuration
     */
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
     * Set leaveCategoryDurationName
     *
     * @param string $leaveCategoryDurationName
     * @return LeaveCategoryDuration
     */
    public function setLeaveCategoryDurationName($leaveCategoryDurationName)
    {
        $this->leaveCategoryDurationName = $leaveCategoryDurationName;

        return $this;
    }

    /**
     * Get leaveCategoryDurationName
     *
     * @return string 
     */
    public function getLeaveCategoryDurationName()
    {
        return $this->leaveCategoryDurationName;
    }
}
