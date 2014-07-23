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
use Opit\OpitHrm\TravelBundle\Entity\TEExpenseType;
use Opit\OpitHrm\CoreBundle\DataFixtures\ORM\AbstractDataFixture;

/**
 * Description of ExpenseTypeFixtures
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class ExpenseTypeFixtures extends AbstractDataFixture
{
    /**
     * {@inheritDoc}
     */
    public function doLoad(ObjectManager $manager)
    {
        $expenseTypes = array('Tickets', 'Taxi', 'Hotel', 'Exchange cost', 'Other');

        foreach ($expenseTypes as $type) {
            $expenseType = new TEExpenseType();
            $expenseType->setName($type);
            $manager->persist($expenseType);

            $this->addReference('expense-type-' . strtolower(str_replace(' ', '-', $type)), $expenseType);
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
