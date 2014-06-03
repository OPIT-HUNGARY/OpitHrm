<?php

/*
 *  This file is part of the LeaveBundle.
 *
 *  (c) Expression license_company is undefined on line 6, column 24 in file:///home/likewise-open/OPIT/bota/NetBeansProjects/notes/nbproject/licenseheader.txt.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\CoreBundle\Entity\AbstractBase;

/**
 * Description of LogTimesheet
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage LeaveBundle
 *
 * @ORM\Table("notes_log_timesheet")
 * @ORM\Entity
 */
class LogTimesheet extends AbstractBase
{
    const PRINTED = 1;
    const DOWNLOADED = 2;
    const EMAILED = 3;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="hash_id", type="string", length=255)
     */
    private $hashId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timesheet_date", type="date")
     */
    private $timesheetDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="action", type="smallint")
     */
    private $action;

    public function __construct()
    {
        parent::__construct();
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
     * Set hashId
     *
     * @param string $hashId
     * @return LogTimesheet
     */
    public function setHashId($hashId)
    {
        $this->hashId = $hashId;

        return $this;
    }

    /**
     * Get hashId
     *
     * @return string
     */
    public function getHashId()
    {
        return $this->hashId;
    }

    /**
     * Set timesheetDate
     *
     * @param \DateTime $timesheetDate
     * @return LogTimesheet
     */
    public function setTimesheetDate($timesheetDate)
    {
        $this->timesheetDate = $timesheetDate;

        return $this;
    }

    /**
     * Get timesheetDate
     *
     * @return \DateTime
     */
    public function getTimesheetDate()
    {
        return $this->timesheetDate;
    }

    /**
     * Set action
     *
     * @param string $action
     * @return LogTimesheet
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
