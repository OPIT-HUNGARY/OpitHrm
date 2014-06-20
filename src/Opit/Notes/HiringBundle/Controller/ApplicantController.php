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
use Opit\Notes\HiringBundle\Entity\Applicant;
use Opit\Notes\HiringBundle\Form\ApplicantType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Opit\Component\Utils\Utils;

class ApplicantController extends Controller
{
    /**
     * To add/edit applicant in Notes
     *
     * @Route("/secured/applicant/show/{id}", name="OpitNotesHiringBundle_applicant_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Template()
     * @throws AccessDeniedException
     */
    public function showJobPositionAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicantId = $request->attributes->get('id');
        $isNewApplicant = 'new' === $applicantId;
        $securityContext = $this->container->get('security.context');
        $isTeamManager = $securityContext->isGranted('ROLE_TEAM_MANAGER');
        $currentUser = $securityContext->getToken()->getUser();
        $isEditable = true;
        $errors = array();


        if (!$isTeamManager) {
            throw new AccessDeniedException(
                'Access denied for applicant.'
            );
        }

        if ($isNewApplicant) {
            $applicant = new Applicant();
        } else {
            $applicant = $entityManager->getRepository('OpitNotesHiringBundle:Applicant')->find($applicantId);
            $isEditable = ($securityContext->isGranted('ROLE_ADMIN') || $currentUser->getId() === $applicant->getCreatedUser()->getId());

            if (null === $applicant) {
                throw $this->createNotFoundException('Missing applicant.');
            }
        }
        $form = $this->createForm(
            new ApplicantType($isNewApplicant), $applicant, array('em' => $entityManager)
        );

        if ($request->isMethod('POST')) {
            if (!$isEditable) {
                throw new AccessDeniedException(
                    'Applicant can not be modified.'
                );
            }

            $form->handleRequest($request);

            if ($form->isValid()) {
                $entityManager->persist($applicant);
                $entityManager->flush();
            } else {
                $errors = Utils::getErrorMessages($form);
            }
        }

        return $this->render(
                'OpitNotesHiringBundle:Applicant:showApplicant.html.twig', array(
                'form' => $form->createView(),
                'isNewApplicant' => $isNewApplicant,
                'isEditable' => $isEditable,
                'errors' => $errors
                )
        );
    }

}
