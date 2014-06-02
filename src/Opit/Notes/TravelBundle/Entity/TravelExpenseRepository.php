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
use Opit\Notes\StatusBundle\Entity\Status;

/**
 * TravelExpenseRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelExpenseRepository extends EntityRepository
{
    /**
     * Get back travel expenses by the given search paramteres.
     *
     * @param array $parameters query parameters
     * @return array data of travel expenses
     */
    public function getTravelExpensesBySearchParams($parameters)
    {
        $qb = $this->createQueryBuilder('te');
        /**
         * Params which will be pass to the setParameter function.
         * @var array
         */
        $params = array();

        if ($parameters['employeeName'] != "") {
            $qb->innerJoin('te.user', 'u')
                ->innerJoin('u.employee', 'e')
                ->andWhere($qb->expr()->like('e.employeeName', ':employeeName'));
            $params['employeeName'] = '%'.$parameters['employeeName'].'%';
        }
        if ($parameters['departureCountry']!="") {
            $params['departureCountry'] = '%'.$parameters['departureCountry'].'%';
              $qb->andWhere($qb->expr()->like('te.departureCountry', ':departureCountry'));
        }
        if ($parameters['arrivalCountry']!="") {
            $params['arrivalCountry'] = '%'.$parameters['arrivalCountry'].'%';
              $qb->andWhere($qb->expr()->like('te.arrivalCountry', ':arrivalCountry'));
        }
        if ($parameters['departureDateFrom']!="") {
            $departureDateFrom = new \DateTime($parameters['departureDateFrom']);
            $params['departureDateFrom'] = $departureDateFrom->format('Y-m-d H:i:s');
            $qb->andWhere($qb->expr()->gte('te.departureDateTime', ':departureDateFrom'));
        }
        if ($parameters['departureDateTo']!="") {
            $departureDateTo = new \DateTime($parameters['departureDateTo']);
            $departureDateTo->add(new \DateInterval('P1D'));
            $params['departureDateTo'] = $departureDateTo->format('Y-m-d H:i:s');
            $qb->andWhere($qb->expr()->lte('te.departureDateTime', ':departureDateTo'));
        }
        if ($parameters['arrivalDateFrom']!="") {
            $arrivalDateFrom = new \DateTime($parameters['arrivalDateFrom']);
            $params['arrivalDateFrom'] = $arrivalDateFrom->format('Y-m-d H:i:s');
            $qb->andWhere($qb->expr()->gte('te.arrivalDateTime', ':arrivalDateFrom'));
        }
        if ($parameters['arrivalDateTo']!="") {
            $arrivalDateTo = new \DateTime($parameters['arrivalDateTo']);
            $arrivalDateTo->add(new \DateInterval('P1D'));
            $params['arrivalDateTo'] = $arrivalDateTo->format('Y-m-d ');
            $qb->andWhere($qb->expr()->lte('te.arrivalDateTime', ':arrivalDateTo'));
        }

        $qb->setParameters($params);
        $q = $qb->getQuery();
        return $q->getResult();
    }

  /*
     * Find employees all travel expense count
     *
     * @param string $userid
     */
    public function findEmployeeTravelExpenseCount($userid)
    {

        $qb = $this->createQueryBuilder('te');
        $qb->select('COUNT(te.id)');
        $qb->where($qb->expr()->eq('te.user', $userid));

        $q = $qb->getQuery();

        return $q->getSingleScalarResult();
    }

  /*
     * Find employees all not pending travel expense count
     *
     * @param string $userid
    */
    public function findEmployeeNotPendingTravelExpense($userid){
        $status = array(Status::APPROVED, Status::PAID, Status::REJECTED);

        $qb = $this->createQueryBuilder('te');

        $qb->select($qb->expr()->countDistinct('te.id'))
        ->leftJoin('te.states', 's')
        ->where($qb->expr()->eq('te.user', ':userId'))
        ->andWhere($qb->expr()->In('s.status', ':states'))
        ->setParameter(':userId', $userid)
        ->setParameter(':states', $status);
        $q = $qb->getQuery();

        return $q->getSingleScalarResult();
    }

}
