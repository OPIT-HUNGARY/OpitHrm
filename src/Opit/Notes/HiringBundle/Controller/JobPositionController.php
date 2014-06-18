<?php

/*
 *  This file is part of the {Bundle}.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\HiringBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\HiringBundle\Form\JobPositionType;
use Opit\Notes\HiringBundle\Entity\JobPosition;

class JobPositionController extends Controller
{

    /**
     * To add/edit job position in Notes
     *
     * @Route("/secured/job/show/{id}", name="OpitNotesHiringBundle_job_position_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function showJobPositionAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $jobPositionId = $request->attributes->get('id');
        $isNewJobPosition = 'new' === $jobPositionId;
        $securityContext = $this->container->get('security.context');
        $isGeneralManager = $securityContext->isGranted('ROLE_GENERAL_MANAGER');

        if ($isNewJobPosition) {
            $jobPosition = new JobPosition();
        } else {
            $jobPosition = $entityManager->getRepository('OpitNotesHiringBundle:JobPosition')->find($jobPositionId);

            if (null === $jobPosition) {
                throw $this->createNotFoundException('Missing job position.');
            }

            if (!$isGeneralManager) {
                throw new AccessDeniedException(
                    'Access denied for job position.'
                );
            }
        }
        
        $form = $this->createForm(
            new JobPositionType($isNewJobPosition),
            $jobPosition,
            array('em' => $entityManager)
        );
        
        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $entityManager->persist($jobPosition);
                $entityManager->flush();
            }
        }
        
        return $this->render(
            'OpitNotesHiringBundle:Job:showJobPosition.html.twig',
            array(
                'form' => $form->createView(),
                'isNewJobPosition' => $isNewJobPosition
            )
        );
    }
}
