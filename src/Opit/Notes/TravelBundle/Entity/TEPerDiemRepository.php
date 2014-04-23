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

/**
 * Description of TEPerDiemRepository
 * 
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TEPerDiemRepository extends EntityRepository
{
    public function findAmountToPay($hours)
    {
        $dq = $this->createQueryBuilder('pd')
                ->where("pd.hours <= (:hours)")
                ->orderBy('pd.hours', 'ASC')
                ->setParameter(':hours', $hours)
                ->getQuery();
                
        $result = $dq->getResult();

        if (0 == count($result)) {
            $result = 0;
        } else {
            $result = $result[count($result)-1]->getAmount();
        }
        
        return $result;
    }
}
