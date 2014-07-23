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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tokens
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage LeaveBundle
 * 
 * @ORM\Table(name="opithrm_leave_tokens")
 * @ORM\Entity
 */
class Token
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    protected $token;
    
    /**
     *
     * @var integer
     * 
     * @ORM\Column(name="leave_id") 
     */
    protected $leaveId;
    
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
     * @param string $token
     * @return \Opit\OpitHrm\LeaveBundle\Entity\Token
     */
    public function setToken($token)
    {
        $this->token = $token;
        
        return $this;
    }
    
    /**
     * Get the token
     * 
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     *
     * @param integer $leaveId
     * @return \Opit\OpitHrm\LeaveBundle\Entity\Token
     */
    public function setLeaveId($leaveId)
    {
        $this->leaveId = $leaveId;
        
        return $this;
    }
    
    /**
     *
     * @return integer
     */
    public function getLeaveId()
    {
        return $this->leaveId;
    }
}
