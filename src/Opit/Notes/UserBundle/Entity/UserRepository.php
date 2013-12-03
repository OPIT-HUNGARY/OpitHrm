<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

/**
 * Description of User
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class UserRepository extends EntityRepository implements UserProviderInterface 
{
    
    /**
     * Method required and called by custom authentication provider
     * 
     * @param string $username The username to look for
     * 
     * @return User $user An user object
     * @throws UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $q = $this
            ->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin AcmeUserBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;
    }

   /**
     * Method required and called by custom authentication provider
     *
     * @param arry $data the data to search for user
     *
     * @return User $user An user object
     */
    public function loadUserByUniques($data)
    {
        $qb = $this->createQueryBuilder('u');

        if (isset($data['id']) && $data['id']>0) {
            $qb->where('(u.username = :username OR u.email = :email OR u.employeeName = :employeeName) AND u.id != :id')
            ->setParameter('username', $data['username'])
            ->setParameter('email', $data['email'])
            ->setParameter('employeeName', $data['employeeName'])
            ->setParameter('id',$data['id']);
        }else{
            $qb->where('u.username = :username OR u.email = :email OR u.employeeName = :employeeName')
            ->setParameter('username', $data['username'])
            ->setParameter('email', $data['email'])
            ->setParameter('employeeName', $data['employeeName']);
        }
        $q = $qb->getQuery();

        return $q->getResult();
    }

    /**
     * Method used by custom authentication provider
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @return User An user object
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    /**
     * Method used by custom authentication provider
     * 
     * @param object $class The class to validate
     * @return boolean Returns true if class is supported
     */
    public function supportsClass($class)
    {
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }
    
    /**
     * Get all Users from
     *
     * @return object User
     */
    public function findAll()
    {
        return $this->findBy(array(), array('username' => 'ASC'));
    }
    
    /**
     * 
     * @param type $parameters key value pairs, parameter name and value
     * @return type
     */
    public function findUsersByPropertyUsingLike($parameters)
    {
        $qb = $this->createQueryBuilder('u');
        $params = array();
        $andx = array();
        
        foreach ($parameters as $key => $value) {
            if ($value != '') {
                $params[':'.$key] = '%'.$value.'%';
                $andx[] = $qb->expr()->andX($qb->expr()->like('u.'.$key, ':'.$key));
            }
        }
        
        $qb->where(call_user_func_array(array($qb->expr(), "andX"), $andx))
        ->setParameters($params);

        $qb = $qb->getQuery();
                
        return $qb->getResult();
    }

    /**
     * Delete users from the database
     *
     * @param arry $data the data contains the id of users
     */
    public function deleteUsersByIds($data)
    {
        $qb = $this
              ->createQueryBuilder('u')
              ->delete()
              ->where('u.id IN (:idarray)')
              ->setParameter('idarray', $data);

        $q = $qb->getQuery();

        return $q->getResult();
    }
}
