<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\HiringBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\OpitHrm\HiringBundle\Entity\Location;

class AdminHiringController extends Controller
{
    /**
     * To list locations in OPIT-HRM
     *
     * @Route("/secured/location/list", name="OpitOpitHrmHiringBundle_location_list")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function listLocationAction(Request $request)
    {
        $orderParams = $this->getRequest()->get('order');

        if ($this->getRequest()->get('issearch')) {
            // Find by order parameters.
            $locations = $this->getDoctrine()->getRepository('OpitOpitHrmHiringBundle:Location')->findBy(
                array(),
                array($orderParams['field'] => $orderParams['dir'])
            );
        } else {
            $locations = $this->getDoctrine()->getRepository('OpitOpitHrmHiringBundle:Location')->findAll();
        }

        if ($request->request->get('showList')) {
            $template = 'OpitOpitHrmHiringBundle:Admin:_locationList.html.twig';
        } else {
            $template = 'OpitOpitHrmHiringBundle:Admin:locationList.html.twig';
        }

        return $this->render(
            $template,
            array(
                'locations' => $locations
            )
        );
    }

    /**
     * To add/edit location in OPIT-HRM
     *
     * @Route("/secured/location/show/{id}", name="OpitOpitHrmHiringBundle_location_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function showLocationAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $locationNameId = $request->attributes->get('id');

        if ('new' === $locationNameId) {
            $location = new Location();
        } else {
            $location =
                $entityManager->getRepository('OpitOpitHrmHiringBundle:Location')->find($locationNameId);
        }

        $location->setName($request->request->get('value'));
        $entityManager->persist($location);
        $entityManager->flush();

        $locations = $this->getDoctrine()->getRepository('OpitOpitHrmHiringBundle:Location')->findAll();

        return $this->render(
            'OpitOpitHrmHiringBundle:Admin:_locationList.html.twig',
            array(
                'locations' => $locations
            )
        );
    }

    /**
     * To delete location in OPIT-HRM
     *
     * @Route("/secured/location/delete", name="OpitOpitHrmHiringBundle_location_delete")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"POST"})
     * @Template()
     */
    public function deleteLocationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('id');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $location = $em->getRepository('OpitOpitHrmHiringBundle:Location')->find($id);
            $em->remove($location);
        }
        $em->flush();

        $locations = $this->getDoctrine()->getRepository('OpitOpitHrmHiringBundle:Location')->findAll();

        return $this->render(
            'OpitOpitHrmHiringBundle:Admin:_locationList.html.twig',
            array(
                'locations' => $locations
            )
        );
    }
}
