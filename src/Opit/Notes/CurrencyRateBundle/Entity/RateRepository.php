<?php

namespace Opit\Notes\CurrencyRateBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * This class is a repository for the Rate entity.
 *
 * @author OPIT Consulting Kft. - NOTES Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage ChangeRateBundle
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
}
