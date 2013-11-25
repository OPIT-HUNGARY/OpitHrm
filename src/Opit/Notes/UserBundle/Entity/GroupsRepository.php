<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\CommonException;

/**
 * Description of RolesRepository
 *
 * @author OPIT Consulting Kft. - PHP/NOTES Team - {@link http://www.opit.hu}
 */
class GroupsRepository extends EntityRepository
{
    /**
     * Finds all groups in the repository.
     *
     * @return array The entities containing name attribute.
     */
    public function findAllGroupNamesArray()
    {
        $dq = $this->createQueryBuilder('g')
                ->select('g.name')
                ->getQuery();
        
        return $dq->getArrayResult();
    }
    
    /**
     * Finds all groups for a user in the repository.
     * 
     * @param int $userId
     * 
     * @return array The entities containing name attribute.
     */
    public function findUserGroupsArray($userId)
    {
        if(!$userId || !is_int($userId))
            throw new CommonException('Given parameter "'.$userId.'" has to be of type integer.');
        
        $dq = $this->createQueryBuilder('g')
                ->select('g.name')
                ->InnerJoin('g.users', 'u', 'WITH', 'u.id = :user_id')
                ->setParameter('user_id', $userId)
                ->getQuery();
                
        return $dq->getArrayResult();
    }
    
    /**
     * Finds groups using IN clause
     * Opitionally the attribute to search on can be passed as a seciond parameter (default: id).
     * 
     * @param array $arr The values used by the IN clause
     * @param type $attribute The attribute to search on
     * 
     * @return array The resulting array containing group objects
     */
    public function findGroupsUsingIn(array $arr, $attribute = 'id')
    {
        $dq = $this->createQueryBuilder('g')
                ->where("g.{$attribute} IN (:values)")
                ->setParameter(':values', $arr)
                ->getQuery();
                
        return $dq->getResult();
    }
    
    /**
     * Finds groups using like searching in names
     * 
     * @param string $chunk Group name chunk used for search
     * @param array $availableRoles Array of roles used to filter on
     * 
     * @return array The resulting array containing group objects
     */
    public function findGroupsByNameUsingLike($chunk, $availableRoles = array())
    {
        $dq = $this->createQueryBuilder('g')
                ->where('g.name LIKE :name')
                ->setParameter(':name', "%{$chunk}%");
                
        if (!empty($availableRoles)) {
            $dq->andWhere('g.role IN (:values)')
                ->setParameter(':values', $availableRoles);
        }
                
        return $dq->getQuery()->getResult();
    }
}
