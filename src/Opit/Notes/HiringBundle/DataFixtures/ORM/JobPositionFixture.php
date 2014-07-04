<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\Notes\StatusBundle\DataFixtures\ORM\AbstractDataFixture;
use Opit\Notes\HiringBundle\Entity\JobPosition;

/**
 * hiring bundle status fixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage Hiring
 */
class JobPositionFixture extends AbstractDataFixture
{
    public function doLoad(ObjectManager $manager)
    {
        $admin = $this->getReference('admin');
        $generalManager = $this->getReference('generalManager');

        $juniorPHPDev = new JobPosition();
        $juniorPHPDev->setDescription('Looking for a junior PHP developer with 5 years or more experience.');
        $juniorPHPDev->setIsActive(true);
        $juniorPHPDev->setJobTitle('Junior PHP developer');
        $juniorPHPDev->setNumberOfPositions(3);
        $juniorPHPDev->setHiringManager($admin);
        $juniorPHPDev->setCreatedUser($admin);

        $seniorPHPDev = new JobPosition();
        $seniorPHPDev->setDescription('Looking for a senior PHP developer under 26 with more than 7 years of relevant experience.');
        $seniorPHPDev->setIsActive(true);
        $seniorPHPDev->setJobTitle('Senior PHP developer');
        $seniorPHPDev->setNumberOfPositions(3);
        $seniorPHPDev->setHiringManager($generalManager);
        $seniorPHPDev->setCreatedUser($generalManager);

        $sysAdmin = new JobPosition();
        $sysAdmin->setDescription('Looking for system administrator.');
        $sysAdmin->setIsActive(false);
        $sysAdmin->setJobTitle('System administrator');
        $sysAdmin->setNumberOfPositions(1);
        $sysAdmin->setHiringManager($generalManager);
        $sysAdmin->setCreatedUser($generalManager);

        $manager->persist($juniorPHPDev);
        $manager->persist($seniorPHPDev);
        $manager->persist($sysAdmin);

        $this->addReference('juniorPHPDeveloper', $juniorPHPDev);
        $this->addReference('seniorPHPDeveloper', $seniorPHPDev);
        $this->addReference('sysAdmin', $seniorPHPDev);

        $manager->flush();
    }

     /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 20; // the order in which fixtures will be loaded
    }

    /**
     *
     * @return array
     */
    protected function getEnvironments()
    {
        return array('prod', 'dev');
    }
}
