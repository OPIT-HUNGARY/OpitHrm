<?php

/*
 *  This file is part of the OPIT-HRM project.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Opit\OpitHrm\TravelBundle\Entity\TEPerDiem;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of PerDiemFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class PerDiemFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $perDiems = array(
            array(8,6),
            array(14,12),
            array(24,24)
            );

        foreach ($perDiems as $key => $value) {
            $perDiem = new TEPerDiem();
            $perDiem->setHours($value[0]);
            $perDiem->setAmount($value[1]);
            $manager->persist($perDiem);
        }

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // the order in which fixtures will be loaded
    }
    
    /**
     * 
     * @return array
     */
    protected function getEnvironments()
    {
        return array('prod', 'dev', 'test');
    }
}
