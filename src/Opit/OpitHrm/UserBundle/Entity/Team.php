<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Description of Team
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage UserBundle
 *
 * @ORM\Table(name="opithrm_teams")
 * @ORM\Entity(repositoryClass="Opit\OpitHrm\UserBundle\Entity\TeamRepository")
 */
class Team
{
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
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Team name can not be blank.")
     */
    protected $teamName;

    /**
     * @ORM\ManyToMany(targetEntity="Employee", mappedBy="teams", cascade = {"persist"})
     */
    protected $employees;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->employees = new \Doctrine\Common\Collections\ArrayCollection();
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
     *
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     *
     * @param type $teamName
     * @return \Opit\OpitHrm\UserBundle\Entity\Teams
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;

        return $this;
    }

    /**
     * Add employees
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\Employee $employees
     * @return Team
     */
    public function addEmployee(\Opit\OpitHrm\UserBundle\Entity\Employee $employees)
    {
        $this->employees[] = $employees;

        return $this;
    }

    /**
     * Remove employees
     *
     * @param \Opit\OpitHrm\UserBundle\Entity\Employee $employees
     */
    public function removeEmployee(\Opit\OpitHrm\UserBundle\Entity\Employee $employees)
    {
        $this->employees->removeElement($employees);
    }

    /**
     * Get employees
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEmployees()
    {
        return $this->employees;
    }
}
