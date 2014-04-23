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

use Doctrine\ORM\EntityRepository;
use Opit\Notes\TravelBundle\Entity\Status;

/**
 * Description of StatusWorkflowRepository
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class StatusWorkflowRepository extends EntityRepository
{
    public function findAvailableStates(Status $parent, $excludes = array())
    {
        $dq = $this->createQueryBuilder('sw');
        
        $dq->where("sw.parent = :parent")
            ->setParameter(':parent', $parent);
        
        if ($excludes) {
            $dq->andWhere(
                $dq->expr()->notIn('sw.status', $excludes)
            );
        }
   
        return $dq->getQuery()->getResult();
    }
}
