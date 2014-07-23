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

/**
 * Description of JobPositionRepository
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @package OPIT-HRM
 * @subpackage HiringBundle
 */
class JobPositionRepository extends EntityRepository
{
    /**
     * @param array $parameters
     * @return object
     */
    public function findAllByFiltersPaginated($pagnationParameters, $parameters = array())
    {
        $orderParams = isset($parameters['order']) ? $parameters['order'] : array();
        $searchParams = isset($parameters['search']) ? $parameters['search'] : array();

        $dq = $this->createQueryBuilder('jp');

        if (isset($searchParams['jobPositionId']) && $searchParams['jobPositionId'] !== '') {
            $dq->andWhere('jp.jobPositionId LIKE :jobPositionId');
            $dq->setParameter(':jobPositionId', '%'.$searchParams['jobPositionId'].'%');
        }

        if (isset($searchParams['jobTitle']) && $searchParams['jobTitle'] !== '') {
            $dq->andWhere('jp.jobTitle LIKE :jobTitle');
            $dq->setParameter(':jobTitle', '%'.$searchParams['jobTitle'].'%');
        }

        if (isset($searchParams['isActive']) && $searchParams['isActive'] !== '') {
            $dq->andWhere('jp.isActive LIKE :isActive');
            $dq->setParameter(':isActive', '%'.$searchParams['isActive'].'%');
        }

        if (isset($orderParams['field']) && $orderParams['field'] && isset($orderParams['dir']) && $orderParams['dir']) {
            $dq->orderBy($orderParams['field'], $orderParams['dir']);
        }

        $dq->setFirstResult($pagnationParameters['firstResult']);
        $dq->setMaxResults($pagnationParameters['maxResults']);

        return new Paginator($dq->getQuery(), true);
    }

    /**
     * Find job title using title and is active
     * 
     * @param string $title
     * @param boolean $isActive
     * @return type
     */
    public function findByTitleLike($title, $isActive = true)
    {
        $dq = $this->createQueryBuilder('jp');
        $dq->where('jp.jobTitle LIKE :title');
        $dq->andWhere('jp.isActive = :isActive');
        $dq->setParameter(':title', '%' . $title . '%');
        $dq->setParameter(':isActive', $isActive);

        return $dq->getQuery()->getResult();
    }
}
