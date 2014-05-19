<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\StatusBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use \Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Provides support for environment specific fixtures.
 *
 * This container aware, abstract data fixture is used to only allow loading in
 * specific environments. The environments the data fixture will be loaded in is
 * determined by the list of environment names returned by `getEnvironments()`.
 *
 * > The fixture will still be shown as having been loaded by the Doctrine
 * > command, `doctrine:fixtures:load`, despite not having been actually
 * > loaded.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
abstract class AbstractDataFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * The dependency injection container.
     *
     * @var ContainerInterface
     */
    protected $container;
    protected $kernel;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var KernelInterface $kernel */
        $this->kernel = $this->container->get('kernel');

        if (in_array($this->kernel->getEnvironment(), $this->getEnvironments())) {
            $this->doLoad($manager);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    protected function getCurrentEnvironment()
    {
        return $this->kernel->getEnvironment();
    }
    
    /**
     * Performs the actual fixtures loading.
     *
     * @see \Doctrine\Common\DataFixtures\FixtureInterface::load()
     *
     * @param ObjectManager $manager The object manager.
     */
    abstract protected function doLoad(ObjectManager $manager);

    /**
     * Returns the environments the fixtures may be loaded in.
     *
     * @return array The name of the environments.
     */
    abstract protected function getEnvironments();
}
