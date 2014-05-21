<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Tokens
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 * 
 * @ORM\Table(name="notes_tokens")
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
     * @ORM\Column(name="travel_id") 
     */
    protected $travelId;
    
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
     * @return \Opit\Notes\TravelBundle\Entity\Token
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
     * @param integer $travelId
     * @return \Opit\Notes\TravelBundle\Entity\Token
     */
    public function setTravelId($travelId)
    {
        $this->travelId = $travelId;
        
        return $this;
    }
    
    /**
     *
     * @return integer
     */
    public function getTravelId()
    {
        return $this->travelId;
    }
}
