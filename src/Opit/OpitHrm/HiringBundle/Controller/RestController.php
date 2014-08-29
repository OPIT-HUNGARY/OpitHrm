<?php

namespace Opit\OpitHrm\HiringBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
* Description of RestController
*
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage HiringBundle
*/
class RestController extends FOSRestController
{
    /**
     * Get job positions action
     *
     * @Rest\Get("/feeds/hiring/jobs.{_format}", name="OpitOpitHrmHiringBundle_api_get_jobpositions", requirements={"_format"="json|xml"}, defaults={"_format"="json"})
     * @Rest\View()
     *
     * @return array
     */
    public function getJobPositionsAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');
        $jobPositions = $entityManager->getRepository('OpitOpitHrmHiringBundle:JobPosition')->findBy(
            array('isActive' => true)
        );

        return array('jobPositions' => $jobPositions);
    }
}
