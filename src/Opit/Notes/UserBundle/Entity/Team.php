<?php

/*
 * The MIT License
 *
 * Copyright 2014 Marton Kaufmann <kaufmann@opit.hu>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * Description of Team
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package Opit
 * @subpackage Notes
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Team
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
     * @ORM\ManyToMany(targetEntity="Employee", mappedBy="teams")
     */
    protected $employee;
    
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
     * @return type
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
     * Get employees
     * 
     * @return type
     */
    public function getEmployees()
    {
        return $this->employee;
    }

    /**
     * Add employees
     * 
     * @param \Opit\Notes\UserBundle\Entity\Employee $employee
     * @return \Opit\Notes\UserBundle\Entity\Team
     */
    public function addEmployee(\Opit\Notes\UserBundle\Entity\Employee $employee)
    {
        $this->employee[] = $employee;
    
        return $this;
    }

    /**
     * Remove employees
     * 
     * @param \Opit\Notes\UserBundle\Entity\Employee $employee
     */
    public function removeUser(\Opit\Notes\UserBundle\Entity\Employee $employee)
    {
        $this->employee->removeElement($employee);
    }
}
