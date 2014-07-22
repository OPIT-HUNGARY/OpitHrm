<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Team
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 *
 * @ORM\Table(name="notes_teams")
 * @ORM\Entity(repositoryClass="Opit\Notes\UserBundle\Entity\TeamRepository")
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
     * @return \Opit\Notes\UserBundle\Entity\Teams
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;

        return $this;
    }

    /**
     * Add employees
     *
     * @param \Opit\Notes\UserBundle\Entity\Employee $employees
     * @return Team
     */
    public function addEmployee(\Opit\Notes\UserBundle\Entity\Employee $employees)
    {
        $this->employees[] = $employees;

        return $this;
    }

    /**
     * Remove employees
     *
     * @param \Opit\Notes\UserBundle\Entity\Employee $employees
     */
    public function removeEmployee(\Opit\Notes\UserBundle\Entity\Employee $employees)
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
