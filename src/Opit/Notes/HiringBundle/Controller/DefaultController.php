<?php

namespace Opit\Notes\HiringBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\HiringBundle\Entity\Token;
use Opit\Notes\StatusBundle\Entity\Status;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Opit\Notes\HiringBundle\Entity\Applicant;
use Opit\Notes\HiringBundle\Form\ExternalApplicantType;
use Opit\Component\Utils\Utils;
use Symfony\Component\Form\FormError;

class DefaultController extends Controller
{
    /**
     * Method to change the status of the travel request or travel expense
     *
     * @Route("/change/applicantstatus/{hmId}/{status}/{token}", name="OpitNotesHiringBundle_change_status", requirements={ "status" = "\d+", "hmId" = "\d+" })
     * @Template()
     * @throws CreateNotFoundException
     */
    public function changeStatusAction(Request $request)
    {
        $method = 'get';
        $error = '';
        $entityManager = $this->getDoctrine()->getManager();
        //get status and Status entity
        $status = $entityManager->getRepository('OpitNotesStatusBundle:Status')
            ->find($request->attributes->get('status'));
        //get token and Token entity
        $token = $entityManager->getRepository('OpitNotesHiringBundle:Token')
            ->findOneBy(array('token' => $request->attributes->get('token')));

        // if $token is not an instance of Token entity throw an exception
        if (false === ($token instanceof Token)) {
            throw $this->createNotFoundException('Security token is not valid. Status cannot be updated.');
        }

        $applicant = $entityManager
            ->getRepository('OpitNotesHiringBundle:Applicant')
            ->find($token->getApplicantId());
        if (null === $applicant) {
            throw $this->createNotFoundException('Missing leave request.');
        }

        if ($request->isMethod('POST')) {
            $method = 'post';

            if (null === $applicant) {
                throw $this->createNotFoundException('Missing leave request.');
            }

            $statusId = $status->getId();
            $applicantId = $applicant->getId();
            $applicant = $entityManager->getRepository('OpitNotesHiringBundle:Applicant')->find($applicantId);
            $numberOfPositions = $applicant->getJobPosition()->getNumberOfPositions();
            $hiredApplicants =
                $entityManager->getRepository('OpitNotesHiringBundle:Applicant')->findHiredApplicantCount($applicant->getJobPosition()->getId());

            if ($hiredApplicants >= $numberOfPositions && Status::HIRED == $statusId) {
                $error = 'No more applicants can be hired for job position';
            } else {
                $entityManager->remove($token);
                $entityManager->flush();

                $status = $this->get('opit.manager.applicant_status_manager')
                    ->addStatus($applicant, $statusId, null);

                $this->get('opit.manager.applicant_notification_manager')
                    ->addNewApplicantNotification($applicant, $status);
            }
        }

        return $this->render(
            'OpitNotesHiringBundle:Default:updateStatus.html.twig',
            array('status' => strtolower($status->getName()), 'method' => $method, 'error' => $error)
        );
    }

    /**
     * Method to create job application from outside of application
     *
     * @Route("/job/application/{token}", name="OpitNotesHiringBundle_job_application", requirements={ "token" })
     * @Template()
     * @throws AccessDeniedException
     */
    public function externalJobApplicationAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $errors = array();
        $token = $request->attributes->get('token');
        $jobPosition = $entityManager->getRepository('OpitNotesHiringBundle:JobPosition')->findOneByExternalToken($token);

        if (null === $jobPosition || false === $jobPosition->getIsActive()) {
            throw new AccessDeniedException(
                'Job position (' . $jobPosition->getJobTitle() . ') is no longer active.'
            );
        }

        $applicant = new Applicant();
        $applicant->setJobPosition($jobPosition);
        $applicant->setApplicationDate(new \DateTime());
        $form = $this->createForm(
            new ExternalApplicantType(), $applicant, array('em' => $entityManager)
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // If new applicant is being added
                // check if applicant has already been added to jp with same email or phone number.
                // Check after form is valid to make sure all data is present.
                if ($entityManager->getRepository('OpitNotesHiringBundle:Applicant')->findByEmailPhoneNumber($applicant) > 0) {
                    $form->addError(new FormError(
                        'Email or phone number has been already registered for this job position.'
                    ));
                    $errors = Utils::getErrorMessages($form);
                } else {
                    $entityManager->persist($applicant);
                    $entityManager->flush();

                    // Send email to applicant
                    $this->get('opit.manager.external_application_email_manager')->sendExternalApplicantMail($jobPosition, $applicant);

                    // Add created status to applicant and send email about it
                    $status = $this->get('opit.manager.applicant_status_manager')->addStatus($applicant, Status::CREATED, null);
                    // Send a notification about new applicant
                    $this->get('opit.manager.applicant_notification_manager')->addNewApplicantNotification($applicant, $status);


                    return $this->render(
                        'OpitNotesHiringBundle:Default:externalApplicationSuccessful.html.twig',
                        array(
                            'jobPosition' => $jobPosition,
                        )
                    );
                }
            } else {
                $errors = Utils::getErrorMessages($form);
            }
        }

        return $this->render(
            'OpitNotesHiringBundle:Default:externalApplication.html.twig',
            array(
                'jobPosition' => $jobPosition,
                'errors' => $errors,
                'form' => $form->createView(),
            )
        );
    }
}
