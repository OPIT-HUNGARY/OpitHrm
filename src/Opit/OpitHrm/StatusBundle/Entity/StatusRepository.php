<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\StatusBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Status Repository
 *
 * Custom repository functions
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage StatusBundle
 */
class StatusRepository extends EntityRepository
{
    /**
     * Returns array with status name as key and id as value.
     * 
     * @return array
     */
    public function getStatusNameId()
    {
        $allStates = $this->findAll();
        $states = array();
        foreach ($allStates as $status) {
            $states[$status->getName()] = $status->getId();
        }
        
        return $states;
    }
    
    public function findStatusCreate()
    {
        return $this->find(Status::CREATED);
    }
}
