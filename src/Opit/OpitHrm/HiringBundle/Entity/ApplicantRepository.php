<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Opit\OpitHrm\StatusBundle\Entity\Status;
use Opit\OpitHrm\HiringBundle\Entity\Applicant;

/**
 * Description of JobPositionRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class ApplicantRepository extends EntityRepository
{
    /**
     * @param array $parameters
     * @return object
     */
    public function findAllByFiltersPaginated($pagnationParameters, $parameters = array())
    {
        $orderParams = isset($parameters['order']) ? $parameters['order'] : array();
        $searchParams = isset($parameters['search']) ? $parameters['search'] : array();

        $dq = $this->createQueryBuilder('a')
            ->innerJoin('a.jobPosition', 'jp');

        if (isset($searchParams['name']) && $searchParams['name'] !== '') {
            $dq->andWhere('a.name LIKE :name');
            $dq->setParameter(':name', '%'.$searchParams['name'].'%');
        }

        if (isset($searchParams['email']) && $searchParams['email'] !== '') {
            $dq->andWhere('a.email LIKE :email');
            $dq->setParameter(':email', '%'.$searchParams['email'].'%');
        }

        if (isset($searchParams['phoneNumber']) && $searchParams['phoneNumber'] !== '') {
            $dq->andWhere('a.phoneNumber LIKE :phoneNumber');
            $dq->setParameter(':phoneNumber', '%'.$searchParams['phoneNumber'].'%');
        }

        if (isset($searchParams['keywords']) && $searchParams['keywords'] !== '') {
            $dq->andWhere('a.keywords LIKE :keywords');
            $dq->setParameter(':keywords', '%'.$searchParams['keywords'].'%');
        }

        if (isset($searchParams['applicationDate']) && $searchParams['applicationDate'] !== '') {
            $dq->andWhere('a.applicationDate LIKE :applicationDate');
            $dq->setParameter(':applicationDate', '%'.$searchParams['applicationDate'].'%');
        }

        if (isset($searchParams['jobTitle']) && $searchParams['jobTitle'] !== '') {
            $dq->andWhere('jp.jobTitle LIKE :jobTitle');
            $dq->setParameter(':jobTitle', '%'.$searchParams['jobTitle'].'%');
        }

        if (isset($orderParams['field']) && $orderParams['field'] && isset($orderParams['dir']) && $orderParams['dir']) {
            $dq->orderBy($orderParams['field'], $orderParams['dir']);
        }

        $dq->setFirstResult($pagnationParameters['firstResult']);
        $dq->setMaxResults($pagnationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    /**
     * Find how many applicants have been hired for a job position
     * 
     * @param integer $jpId
     * @return type
     */
    public function findHiredApplicantCount($jpId)
    {
        $dq = $this->createQueryBuilder('a');
        $dq->select('count(a.id)');
        $dq->innerJoin('a.jobPosition', 'jp');
        $dq->innerJoin('a.states', 'states');
        $dq->innerJoin('states.status', 'status');
        $dq->where($dq->expr()->eq('jp.id', ':jpId'));
        $dq->andWhere($dq->expr()->eq('status.id', ':hiredState'));
        $dq->setParameter(':jpId', $jpId);
        $dq->setParameter(':hiredState', Status::HIRED);

        return $dq->getQuery()->getSingleScalarResult();
    }

    /**
     * Check if applicant with email or phone number has been added to a job position
     * 
     * @param \Opit\OpitHrm\HiringBundle\Entity\Applicant $applicant
     * @return type
     */
    public function findByEmailPhoneNumber(Applicant $applicant)
    {
        $dq = $this->createQueryBuilder('a');
        $dq->select('count(a.id)');
        $dq->innerJoin('a.jobPosition', 'jp');
        $dq->where($dq->expr()->eq('jp.id', ':jpId'));
        $dq->andWhere(
            $dq->expr()->orX(
                $dq->expr()->eq('a.email', ':email'),
                $dq->expr()->eq('a.phoneNumber', ':phoneNumber')
            )
        );

        $dq->setParameter(':email', $applicant->getEmail());
        $dq->setParameter(':phoneNumber', $applicant->getPhoneNumber());
        $dq->setParameter(':jpId', $applicant->getJobPosition()->getId());

        return $dq->getQuery()->getSingleScalarResult();
    }
}
