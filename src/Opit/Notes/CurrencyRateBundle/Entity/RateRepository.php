<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\CurrencyRateBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Opit\Notes\CurrencyRateBundle\Entity\Rate;

/**
 * This class is a repository for the Rate entity.
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage CurrencyRateBundle
 */
class RateRepository extends EntityRepository
{
    /**
     * Has an existing rate in the database with the passed datetime and currency
     * 
     * @param string $code the currency code
     * @param \DateTime $datetime the datetime of the searching
     * @return boolean
     */
    public function hasRate($code, \DateTime $datetime)
    {
        $qb = $this->createQueryBuilder('r')
                   ->select('COUNT(r.id)');
        $this->setQueriyBuilderConditons($qb, $code, $datetime);
        $q = $qb->getQuery();
        
        return (bool) $q->getSingleScalarResult();
    }
    
    /**
     * Find the last rate.
     * 
     * @return Rate A rate instance.
     */
    public function findLastRate()
    {
        $qb = $this->createQueryBuilder('r')
                   ->orderBy('r.created', 'DESC')
                   ->setMaxResults(1);
        $q = $qb->getQuery();
        
        return $q->getOneOrNullResult();
    }
    
    /**
     * Find the first rate.
     * 
     * @return Rate A rate instance.
     */
    public function findFirstRate()
    {
        $qb = $this->createQueryBuilder('r')
                   ->orderBy('r.created', 'ASC')
                   ->setMaxResults(1);
        $q = $qb->getQuery();
        
        return $q->getOneOrNullResult();
    }
    
    /**
     * Find rate entity by currency code and datetime
     * 
     * @param string $code currency code
     * @param \DateTime $datetime searched datetime
     * @return Rate entity
     */
    public function findRateByCodeAndDate($code, \DateTime $datetime)
    {
        $qb = $this->createQueryBuilder('r');
        $this->setQueriyBuilderConditons($qb, $code, $datetime);
        $q = $qb->getQuery();
        
        return $q->getOneOrNullResult();
    }
    
    /**
     * Set the query builder object with conditions.
     * 
     * @param QueryBuilder $qb
     * @param string $code the currency code
     * @param \DateTime $datetime the searched datetime
     */
    private function setQueriyBuilderConditons(&$qb, $code, \DateTime $datetime)
    {
        //create datetime intervallum
        $datetimeCopy = clone $datetime;
        $start = $datetime->setTime(0, 0, 0);
        $end = $datetimeCopy->setTime(23, 59, 59);
        
        //set the common conditions.
        $qb->where('r.currencyCode = :code')
            ->andWhere('r.created >= :start AND r.created <= :end')
            ->setParameter('code', $code)
            ->setParameter('start', $start)
            ->setParameter('end', $end);
    }
    
    public function getRatesArray(\DateTime $date)
    {
        $rarrRates = array();
        $created = clone $date;
        // Set time to 0 to match all days rates
        $rates = $this->findByCreated($created->setTime(0, 0, 0));
        
        foreach ($rates as $rate) {
            $rarrRates[$rate->getCurrencyCode()->getCode()] = $rate->getRate();
        }
        
        return $rarrRates;
    }
}
