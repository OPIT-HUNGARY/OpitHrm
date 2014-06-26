<?php

namespace Opit\Notes\HiringBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\HiringBundle\Entity\Token;
use Opit\Notes\StatusBundle\Entity\Status;

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
            'OpitNotesHiringBundle:Shared:updateStatus.html.twig',
            array('status' => strtolower($status->getName()), 'method' => $method, 'error' => $error)
        );
    }

}
