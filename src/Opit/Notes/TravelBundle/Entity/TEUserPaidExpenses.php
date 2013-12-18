<?php

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Opit\Notes\TravelBundle\Entity\TEPaidExpenses;

/**
 * TEUserPaidExpenses
 *
 * @ORM\Table(name="notes_te_user_pe")
 * @ORM\Entity
 */
class TEUserPaidExpenses extends TEPaidExpenses
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
     * @ORM\Column(name="justification", type="string", length=255)
     */
    protected $justification;


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
     * Set justification
     *
     * @param string $justification
     * @return TEUserPaidExpenses
     */
    public function setJustification($justification)
    {
        $this->justification = $justification;
    
        return $this;
    }

    /**
     * Get justification
     *
     * @return string
     */
    public function getJustification()
    {
        return $this->justification;
    }    
}