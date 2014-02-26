<?php

/*
 * This file is part of the Travel bundle.
 *
 * (c) Opit Consulting Kft. <info@opit.hu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\CommonException;
use Opit\Notes\TravelBundle\Entity\Status;

/**
 * Status Repository
 *
 * Custom repository functions
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage TravelBundle
 */
class StatusRepository extends EntityRepository
{
    /**
     * Finds the latest status of a travel request.
     *
     * @param integer $trId
     * @return mixed A status object or null
     */
    public function findLastByTravelRequest($trId)
    {

    }

    /**
     * Finds the latest status of a travel expense.
     *
     * @param integer $teId
     * @return mixed A status object or null
     */
    public function findLastByTravelExpense($teId)
    {

    }
    
    public function findStatusCreate()
    {
        return $this->find(Status::CREATED);
    }
}
