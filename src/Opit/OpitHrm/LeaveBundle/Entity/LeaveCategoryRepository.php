<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\LeaveBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Description of LeaveCategoryRepository
 */
class LeaveCategoryRepository extends EntityRepository
{
    /**
     * Find all leave categories not counted as leaves
     *
     * @return array
     */
    public function findNotCountedAsLeaveIds()
    {
        $qb = $this->createQueryBuilder('lc');
        $qb->select('lc.id')
            ->where('lc.isCountedAsLeave = 0');

        return $qb->getQuery()->getArrayResult();
    }
}
