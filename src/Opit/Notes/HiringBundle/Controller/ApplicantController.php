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
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\HiringBundle\Entity\Applicant;
use Opit\Notes\HiringBundle\Form\ApplicantType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
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
    public function showApplicantAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicantId = $request->attributes->get('id');
        $isNewApplicant = 'new' === $applicantId;
        $securityContext = $this->container->get('security.context');
        $isTeamManager = $securityContext->isGranted('ROLE_TEAM_MANAGER');
        $entityManager->getFilters()->disable('softdeleteable');
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
            $isEditable = (
                ($securityContext->isGranted('ROLE_ADMIN') || $currentUser->getId() === $applicant->getCreatedUser()->getId()) &&
                null === $applicant->getJobPosition()->getDeletedAt()
            );

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

                return $this->redirect($this->generateUrl('OpitNotesHiringBundle_applicant_list'));
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

    /**
     * To list applicant in Notes
     *
     * @Route("/secured/applicant/list", name="OpitNotesHiringBundle_applicant_list")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Template()
     * @throws AccessDeniedException
     */
    public function listApplicantAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->getFilters()->disable('softdeleteable');
        $securityContext = $this->container->get('security.context');
        $isSearch = $request->request->get('issearch');
        $searchRequests = array();
        $config = $this->container->getParameter('pager_config');
        $maxResults = $config['max_results'];
        $offset = $request->request->get('offset');

        if (!$securityContext->isGranted('ROLE_TEAM_MANAGER')) {
            throw new AccessDeniedException(
                'Access denied for job position listing.'
            );
        }

        if ($isSearch) {
            $searchRequests = $request->request->all();
        }

        $pagnationParameters = array(
            'firstResult' => ($offset * $maxResults),
            'maxResults' => $maxResults
        );

        $applicants = $entityManager->getRepository('OpitNotesHiringBundle:Applicant')
            ->findAllByFiltersPaginated($pagnationParameters, $searchRequests);

        if ($request->request->get('resetForm') || $isSearch || null !== $offset) {
            $template = 'OpitNotesHiringBundle:Applicant:_list.html.twig';
        } else {
            $template = 'OpitNotesHiringBundle:Applicant:list.html.twig';
        }

        return $this->render(
            $template,
            array('applicants' => $applicants)
        );
    }

    /**
     * To delete applicant in Notes
     *
     * @Route("/secured/applicant/delete", name="OpitNotesHiringBundle_applicant_delete")
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Template()
     * @throws AccessDeniedException
     */
    public function deleteApplicantAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        $entityManager = $this->getDoctrine()->getManager();
        $currentUser = $securityContext->getToken()->getUser();
        $ids = $request->request->get('id');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $applicant = $entityManager->getRepository('OpitNotesHiringBundle:Applicant')->find($id);

            if (!$securityContext->isGranted('ROLE_ADMIN') || $currentUser->getId() !== $applicant->getCreatedUser()->getId()) {
                throw new AccessDeniedException(
                    'Access denied for applicant.'
                );
            } else {
                unlink($applicant->getAbsolutePath());
                $entityManager->remove($applicant);
            }
        }
        $entityManager->flush();

        return new JsonResponse('success');
    }

    /**
     * To download applicant cv in Notes
     *
     * @Route("/secured/applicant/cv/download/{id}", name="OpitNotesHiringBundle_applicant_cv_download", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_TEAM_MANAGER")
     * @Template()
     * @throws AccessDeniedException
     */
    public function applicantCVDownloadAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicant = $entityManager->getRepository('OpitNotesHiringBundle:Applicant')->find($request->attributes->get('id'));

        $CV = $applicant->getAbsolutePath();
        $response = new Response();
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-type', mime_content_type($CV));
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($CV) . '";');
        $response->headers->set('Content-length', filesize($CV));
        $response->sendHeaders();
        $response->setContent(readfile($CV));
    }
}
