<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Security\Ldap;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\SimpleFormAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Psr\Log\LoggerInterface;
use Zend\Ldap\Ldap;

/**
 * Description of LdapAuthenticator
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class LdapAuthenticator implements SimpleFormAuthenticatorInterface
{

    private $encoderFactory;
    private $ldapManager;
    private $logger;

    public function __construct(EncoderFactoryInterface $encoderFactory, Ldap $ldapManager = null, LoggerInterface $logger = null)
    {
        $this->encoderFactory = $encoderFactory;
        $this->ldapManager = $ldapManager;
        $this->logger = $logger;
    }

    /**
     * Function used for user authentication based on token object
     *
     * @param  \Symfony\Component\Security\Core\Authentication\Token\TokenInterface        $token
     * @param  \Symfony\Component\Security\Core\User\UserProviderInterface                 $userProvider
     * @param  type                                                                        $providerKey
     * @return \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken
     * @throws BadCredentialsException
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $passwordValid = false;

        // Loda user object
        try {
            $user = $userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            throw new BadCredentialsException('Invalid username or password', 0, $e);
        }

        // Check if ldap extension is enabled and user's ldap flag is set.
        if (null !== $this->ldapManager && $user->isLdapEnabled()) {
            try {
                $this->ldapManager->bind($token->getUsername(), $token->getCredentials());
                $passwordValid = (boolean) $this->ldapManager->getBoundUser();

                if (null !== $this->logger && !$token->isAuthenticated()) {
                    $this->logger->info(
                        "[LdapAuthenticator] Ldap authentication successful.",
                        array('user' => $this->ldapManager->getBoundUser())
                    );
                }
            } catch (\Zend\Ldap\Exception\LdapException $e) {
                throw new BadCredentialsException('Invalid username or password', 0, $e);
            }
        } else {
            $currentUser = $token->getUser();

            if ($currentUser instanceof UserInterface) {
                if ($currentUser->getPassword() !== $user->getPassword()) {
                    throw new BadCredentialsException('The credentials were changed from another session.');
                } else {
                    $passwordValid = true;
                }
            } else {
                if ("" === ($presentedPassword = $token->getCredentials())) {
                    throw new BadCredentialsException('Invalid username or password.');
                }

                if (!$passwordValid = $this->encoderFactory->getEncoder($user)->isPasswordValid($user->getPassword(), $presentedPassword, $user->getSalt())) {
                    throw new BadCredentialsException('Invalid username or password.');
                }
            }

            if (null !== $this->logger && !$token->isAuthenticated()) {
                $this->logger->info(
                    "[LdapAuthenticator] Local authentication successful.",
                    array('user' => $user->getUsername())
                );
            }
        }

        // Set the authenticated token
        if ($passwordValid) {
            return new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
        }

        throw new BadCredentialsException('Invalid username or password');
    }

    /**
     * {@inheritdoc}
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof UsernamePasswordToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * {@inheritdoc}
     */
    public function createToken(Request $request, $username, $password, $providerKey)
    {
        return new UsernamePasswordToken($username, $password, $providerKey);
    }
}
