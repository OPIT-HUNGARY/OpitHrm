<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\CoreBundle\Security\Authorization;

use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Description of AclManager
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage CoreBundle
 */
class AclManager
{
    const ACTION_ADDED = 'added';
    const ACTION_EDITED = 'edited';
    const ACTION_REMOVED = 'removed';

    protected $provider;
    protected $logger;

    /**
     * Constructor
     *
     * @param AclProviderInterface $provider
     */
    public function __construct(AclProviderInterface $provider, LoggerInterface $logger = null)
    {
        $this->provider = $provider;
        $this->logger = $logger;
    }

    /**
     * Retrieves the security identity objects
     *
     * @param object $object The object to get the acl from
     * @param mixed $identities Considered security identities
     *
     * @return array Security identities grouped by classname
     */
    public function findSecurityIdenties($object, $grouped = false, $identities = array('UserSecurityIdentity', 'RoleSecurityIdentity'))
    {
        if (!is_array($identities)) {
            $identities = array($identities);
        }

        // Create identities and get ACL
        $oid = ObjectIdentity::fromDomainObject($object);
        $acl = $this->getAcl($oid);
        $sids = array();

        foreach ($acl->getObjectAces() as $ace) {
            foreach ($identities as $identity) {
                $classIdentity = 'Symfony\Component\Security\Acl\Domain\\'.$identity;
                if ($ace->getSecurityIdentity() instanceof $classIdentity) {
                    if ($grouped) {
                        $sids[$identity][] = $ace->getSecurityIdentity();
                    } else {
                        $sids[] = $ace->getSecurityIdentity();
                    }
                }
            }
        }

        return $sids;
    }

    /**
     * Retrieves an array of names grouped by security identity
     *
     * @param object $object
     * @param array $identities
     *
     * @return array The resulting array containing names
     */
    public function getNamesforIdentities($object, array $identities = array('UserSecurityIdentity' => 'getUsername', 'RoleSecurityIdentity' => 'getRole'))
    {
        $result = array();
        $sids = $this->findSecurityIdenties($object, true, array_keys($identities));

        foreach ($identities as $identity => $method) {
            $result[$identity] = array();

            if (isset($sids[$identity])) {
                foreach ($sids[$identity] as $sid) {
                    $result[$identity][] = $sid->$method();
                }
            }
        }

        return $result;
    }

    /**
     * Grants a permission to $identity for $object
     * Will check for existing permissions and update accordingly
     *
     * @param object $object
     * @param object $identity
     * @param int $mask
     */
    public function grant($object, $identity, $mask = MaskBuilder::MASK_OWNER)
    {
        // Create identities and get ACL
        $oid = ObjectIdentity::fromDomainObject($object);
        $sid = $this->getSecurityIdentity($identity);
        $acl = $this->getAcl($oid);
        $action = '';

        // Try to find an ACE for the user
        // If none exists, create one.
        list($i, $ace) = $this->getAce($acl, $sid);

        if (isset($ace)) {
            if ($mask === 0) {
                $acl->deleteObjectAce($i);
                $action = self::ACTION_REMOVED;
            } else {
                // We found an ACE. If the mask's the same, we just stop here
                if ($ace->getMask() == $mask) {
                    return;
                }

                $acl->updateObjectAce($i, $mask);
                $action = self::ACTION_EDITED;
            }
        } else {
            // If mask equals 0, we just stop here
            if ($mask === 0) {
                return;
            }

            $acl->insertObjectAce($sid, $mask);
            $action = self::ACTION_ADDED;
        }

        $this->provider->updateAcl($acl);

        if (null !== $this->logger) {
            $this->logger->info(
                "[ACL MANAGER] ". (($action == 'removed') ? 'Revoked' : 'Granted') .
                " access to " . ClassUtils::getRealClass($object) . ". Entry {$action}, mask: {$mask}."
            );
        }
    }

    /**
     * Revokes all permissions to $identity for $object
     *
     * @param $object
     * @param $identity
     */
    public function revoke($object, $identity)
    {
        // Create identities and get ACL
        $oid = ObjectIdentity::fromDomainObject($object);
        $sid = ($identity instanceof SecurityIdentityInterface) ? $identity : $this->getSecurityIdentity($identity);
        $acl = $this->getAcl($oid, false);

        // Get the ACE
        list($i, $ace) = $this->getAce($acl, $sid);

        if (isset($ace)) {
            $acl->deleteObjectAce($i);

            $this->provider->updateAcl($acl);

            if (null !== $this->logger) {
                $this->logger->info(
                    "[ACL MANAGER] Revoked access to " . ClassUtils::getRealClass($object) .
                    ". Entry {self::ACTION_REMOVED}."
                );
            }
        }
    }

    /**
     * Revokes all permissions to all identities of $object
     *
     * @param type $object
     */
    public function revokeAll($object)
    {
        $sids = $this->findSecurityIdenties($object);

        foreach ($sids as $sid) {
            $this->revoke($object, $sid);
        }
    }

    /**
     * Deletes the ACL of $object
     *
     * @param type $object
     */
    public function deleteAcl($object)
    {
        $oid = ObjectIdentity::fromDomainObject($object);

        $this->provider->deleteAcl($oid);
    }

    /**
     * Get an ACL for an object and a user
     * If createEmpty is false, it will throw an error if it can't find the ACL
     *
     * @param object $oid
     * @param object $sid
     * @param bool $createEmpty
     * @throws \Exception
     *
     * @return \Symfony\Component\Security\Acl\Model\MutableAclInterface
     */
    protected function getAcl($oid, $createEmpty = true)
    {
        // Try to find an existing ACL, if none exists, create one.
        try {
            return $this->provider->findAcl($oid);
        } catch (AclNotFoundException $e) {
            if ($createEmpty) {
                return $this->provider->createAcl($oid);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Gets the user ACE
     *
     * It returns the index of the ace and the ace itself. We sadly need the index as well.
     *
     * @param object $acl
     * @param object $sid
     *
     * @return array The index and access controle entry object
     */
    protected function getAce($acl, $sid)
    {
        $index = null;
        $ace = null;

        foreach ($acl->getObjectAces() as $i => $maybeAce) {
            if ($sid->equals($maybeAce->getSecurityIdentity())) {
                $index = $i;
                $ace = $maybeAce;
                break; // stop the loop, we have our ace
            }
        }

        return array($index, $ace);
    }

    /**
     * Gets the security identity
     *
     * Supported are Group and User entities
     *
     * @param object $identity
     * @return object  Returns either a Role or User security identity
     * @throws \Exception
     */
    protected function getSecurityIdentity($identity)
    {
        if ($identity instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            $sid = UserSecurityIdentity::fromAccount($identity);
        } elseif ($identity instanceof \Symfony\Component\Security\Core\Role\RoleInterface) {
            $sid = new RoleSecurityIdentity($identity->getRole());
        } else {
            throw new Exception(sprintf(
                'The AclManager couldn\'t find a matching security identity for %s.',
                get_class($identity)
            ));
        }

        return $sid;
    }

    /**
     * Updates a user security identity when the user's username changes
     * 
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param string $oldUsername
     */
    public function updateUserSecurityIdentity(UserInterface $user, $oldUsername)
    {
        $securityIdentity = $this->getSecurityIdentity($user);
        $this->provider->updateUserSecurityIdentity($securityIdentity, $oldUsername);
    }
}
