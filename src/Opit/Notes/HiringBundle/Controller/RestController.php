<?php

namespace Opit\Notes\HiringBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
* Description of RestController
*
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
*/
class RestController extends FOSRestController
{
    /**
     * Get job positions action
     *
     * @Rest\Get("/feeds/hiring/jobs.{_format}", name="OpitNotesHiringBundle_api_get_jobpositions", requirements={"_format"="json|xml|html"}, defaults={"_format"="json"})
     * @Rest\View()
     *
     * @return array
     */
    public function getJobPositionsAction()
    {
        $jobPositions = $this->getDoctrine()->getRepository('OpitNotesHiringBundle:JobPosition')->findBy(
            array('isActive' => true)
        );

        return array('jobPositions' => $jobPositions);
    }
}
