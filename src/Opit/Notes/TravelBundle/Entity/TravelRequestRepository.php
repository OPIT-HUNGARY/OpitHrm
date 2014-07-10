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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Opit\Notes\StatusBundle\Entity\Status;

/**
 * TravelRequestRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class TravelRequestRepository extends EntityRepository
{
    /**
     * Get back travel requests by the given search paramteres.
     *
     * @param array $pagnationParameters Pagination parameters
     * @param array $parameters Query parameters
     * @return \Doctrine\ORM\Tools\Pagination\Paginator Travel requests paginator object
     * @todo create search opportunity on TeamManager name
     * @todo create search opportunity on GeneralManager name
     */
    public function findAllByFiltersPaginated($pagnationParameters, $parameters = array())
    {
        $params = array();
        $whereParams = isset($parameters['search']) ? $parameters['search'] : array();
        $orderParams = isset($parameters['order']) ? $parameters['order'] : array();
        
        $qb = $this->createQueryBuilder('tr')->distinct();

        if (isset($whereParams['trId']) && $whereParams['trId'] != "") {
            $params['trId'] = '%'.$whereParams['trId'].'%';
            $qb->andWhere($qb->expr()->like('tr.travelRequestId', ':trId'));
        }
        if (isset($whereParams['employeeName']) && $whereParams['employeeName'] != "") {
            $params['employeeName'] = '%'.$whereParams['employeeName'].'%';
            $qb->innerJoin('tr.user', 'u')
                ->innerJoin('u.employee', 'e')
                ->andWhere($qb->expr()->like('e.employeeName', ':employeeName'));
        }
        if (isset($whereParams['customerName']) && $whereParams['customerName'] != "") {
            $params['customerName'] = '%' . $whereParams['customerName'] . '%';
            $qb->andWhere($qb->expr()->like('tr.customerName', ':customerName'));
        }
        if (isset($whereParams['destinationName']) && $whereParams['destinationName'] != "") {
            $params['destinationName'] = '%' . $whereParams['destinationName'] . '%';
            $qb->innerJoin('tr.destinations', 'd');
            $qb->andWhere($qb->expr()->like('d.name', ':destinationName'));
        }
        if (isset($whereParams['departureDateFrom']) && $whereParams['departureDateFrom'] != "") {
            $params['departureDateFrom'] = $whereParams['departureDateFrom'];
            $qb->andWhere($qb->expr()->gte('tr.departureDate', ':departureDateFrom'));
        }
        if (isset($whereParams['departureDateTo']) && $whereParams['departureDateTo'] != "") {
            $params['departureDateTo'] = $whereParams['departureDateTo'];
            $qb->andWhere($qb->expr()->lte('tr.departureDate', ':departureDateTo'));
        }
        if (isset($whereParams['arrivalDateFrom']) && $whereParams['arrivalDateFrom'] != "") {
            $params['arrivalDateFrom'] = $whereParams['arrivalDateFrom'];
            $qb->andWhere($qb->expr()->gte('tr.arrivalDate', ':arrivalDateFrom'));
        }
        if (isset($whereParams['arrivalDateTo']) && $whereParams['arrivalDateTo'] != "") {
            $params['arrivalDateTo'] = $whereParams['arrivalDateTo'];
            $qb->andWhere($qb->expr()->lte('tr.arrivalDate', ':arrivalDateTo'));
        }
        
        $params['user'] = $pagnationParameters['currentUser'];

        if (!$pagnationParameters['isAdmin']) {
            // If general manager, filter created travel requests unless current user is the owner
            if ($pagnationParameters['isGeneralManager']) {
                $params['status'] = Status::CREATED;
                $statusExpr = $qb->expr()->orX(
                    $qb->expr()->andX(
                        $qb->expr()->notIn('s.status', ':status'),
                        $qb->expr()->eq('tr.generalManager', ':user')
                    ),
                    $qb->expr()->eq('tr.user', ':user')
                );
                $qb->leftJoin('tr.states', 's')
                    ->andWhere($statusExpr);
            } else {
                $qb->andWhere($qb->expr()->eq('tr.user', ':user'));
            }
        } else {
            unset($params['user']);
        }
        

        $qb->setParameters($params)
            ->setFirstResult($pagnationParameters['firstResult'])
            ->setMaxResults($pagnationParameters['maxResults']);
        
        if (isset($orderParams['field']) && $orderParams['field'] && isset($orderParams['dir']) && $orderParams['dir']) {
            $qb->orderBy('tr.'.$orderParams['field'], $orderParams['dir']);
        }
 
        return new Paginator($qb->getQuery(), true);
    }
    
    /**
     * Find all travel request with ordering by fields.
     * 
     * @param string $field
     * @param string $order
     * @return null|TravelRequest
     */
    public function findAllOrderByField($field, $order)
    {
        if (!isset($field) || !isset($order) ||empty($field) || empty($order)) {
            return null;
        }
        
        $qb = $this->createQueryBuilder('tr');
        
        if ("user"===$field) {
            $qb->leftJoin('tr.user', 'u');
            $qb->orderBy('u.employeeName', $order);
        } else {
             $qb->orderBy('tr.'.$field, $order);
        }
       
        $q = $qb->getQuery();
        
        return $q->getResult();
    }

   /*
     * Find employees all travel requests count
     * 
     * @param string $userid
    */
    public function findEmployeeTravelRequest($userid){

        $qb = $this->createQueryBuilder('tr');
        $qb->select('COUNT(tr.id)');
        $qb->where($qb->expr()->eq('tr.user', $userid));

        $q = $qb->getQuery();

        return $q->getSingleScalarResult();
    }

    /*
     * Find employees all not pending travel requests count
     *
     * @param string $userid
    */
    public function findEmployeeNotPendingTravelRequest($userid){
        $status = array(Status::APPROVED, Status::PAID, Status::REJECTED);

        $qb = $this->createQueryBuilder('tr');

        $qb->select($qb->expr()->countDistinct('tr.id'))
        ->leftJoin('tr.states', 's')
        ->where($qb->expr()->eq('tr.user', ':userId'))
        ->andWhere($qb->expr()->In('s.status', ':states'))
        ->setParameter(':userId', $userid)
        ->setParameter(':states', $status);
        $q = $qb->getQuery();

        return $q->getSingleScalarResult();
    }
}
