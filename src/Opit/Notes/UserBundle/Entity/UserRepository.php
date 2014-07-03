<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Opit\Component\Utils\Utils;

/**
 * Description of User
 * Custom user entity to validata against a database
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
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
    public function findUsersByPropertyUsingLike($parameters, $firstResult, $maxResults)
    {
        $qb = $this->createQueryBuilder('u');
        $params = array();
        $andx = array();
        $orderParams = isset($parameters['order']) ? $parameters['order'] : array();

        // Build the query from posted search parameters
        Utils::buildDoctrineQuery($qb, $parameters['search'], $params, $andx);

        // Only apply where if parameters are given
        if (count($andx) > 0) {
            $qb->where(call_user_func_array(array($qb->expr(), "andX"), $andx))
                ->setParameters($params);
        }

        if (isset($orderParams['field']) && $orderParams['field'] && isset($orderParams['dir']) && $orderParams['dir']) {
            $qb->orderBy($orderParams['field'], $orderParams['dir']);
        }

        $qb->setFirstResult($firstResult);
        $qb->setMaxResults($maxResults);

        return new Paginator($qb->getQuery(), true);
    }

    /**
     * Get users by an array which contain the ids.
     *
     * @param array $userIds contains the ids of users
     * @return User $user user object list.
     */
    public function findUsersUsingIn($userIds)
    {
        $qb = $this
              ->createQueryBuilder('u')
              ->where('u.id IN (:idarray)')
              ->setParameter('idarray', $userIds);

        $q = $qb->getQuery();

        return $q->getResult();
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

    /**
     * Finds users by employee name
     *
     * {@inheritDoc}
     * @return array
     */
    public function findUserByEmployeeNameUsingLike($chunk, $role = "ROLE_USER")
    {
        $q = $this->getFindUserBaseQueryBuilder($chunk, $role);

        return $q->getQuery()->getResult();
    }

    /**
     * Finds team managers of the given user by employee name
     *
     * @param mixed $id User object or id
     * {@inheritDoc}
     *
     * @return array
     */
    public function findTeamManagersUsingLike($id, $chunk, $role = "ROLE_TEAM_MANAGER")
    {
        $dq = $this->createQueryBuilder('u0')
            ->select('t0.id')
            ->innerJoin('u0.employee', 'e0')
            ->innerJoin('e0.teams', 't0')
            ->where('u0.id = :id');

        $dq2 = $this->getFindUserBaseQueryBuilder($chunk, $role);
        $dq2->leftJoin('e.teams', 't');

        $teamManagers = $dq2
            ->andWhere($dq2->expr()->in('t.id', $dq->getDQL()))
            ->groupBy('e.id')
            ->setParameter('id', $id)
            ->getQuery();

        return $teamManagers->getResult();
    }

    /**
     * Get Pagination
     *
     * @param integer $firstResult
     * @param integer $maxResults
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPaginaton($firstResult, $maxResults)
    {
        $users = $this->createQueryBuilder('user')
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->getQuery();

        return new Paginator($users, $fetchJoinCollection = true);
    }

    /**
     * Builds the base query for user search
     *
     * @param string $chunk part of the employee name or email
     * @param mixed $role String or array of role names
     * @return object
     */
    protected function getFindUserBaseQueryBuilder($chunk, $role)
    {
        $role = Utils::arrayValuesToUpper($role);

        $q = $this->createQueryBuilder('u')
            ->leftJoin('u.employee', 'e')
            ->innerJoin('u.groups', 'g')
            ->where('e.employeeName LIKE :employeeName')
            ->orWhere('u.email LIKE :email')
            ->andWhere('g.role IN (:role)')
            ->setParameter('employeeName', "%{$chunk}%")
            ->setParameter('email', "%{$chunk}%")
            ->setParameter('role', $role);

        return $q;
    }
}
