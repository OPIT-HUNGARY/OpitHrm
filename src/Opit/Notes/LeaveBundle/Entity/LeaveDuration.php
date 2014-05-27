<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leave duration
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeavenBundle
 *
 * @ORM\Table(name="notes_leave_duration")
 * @ORM\Entity()
 */
class LeaveDuration
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
     * @ORM\Column(name="leave_duration_name", type="string", length=255)
     */
    protected $leaveDurationName;

    /**
     * Set id
     *
     * @param integer $id
     * @return LeaveDuration
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
     * Set leaveDurationName
     *
     * @param string $leaveDurationName
     * @return LeaveDuration
     */
    public function setLeaveDurationName($leaveDurationName)
    {
        $this->leaveDurationName = $leaveDurationName;

        return $this;
    }

    /**
     * Get leaveDurationName
     *
     * @return string 
     */
    public function getLeaveDurationName()
    {
        return $this->leaveDurationName;
    }
}
