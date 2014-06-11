<?php

namespace Opit\Notes\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Opit\Notes\LeaveBundle\Entity\Token;

class DefaultController extends Controller
{
    /**
     * Method to change the status of the travel request or travel expense
     *
     * @Route("/change/leavestatus/{gmId}/{status}/{token}", name="OpitNotesLeaveBundle_change_status", requirements={ "status" = "\d+", "gmId" = "\d+" })
     * @Template()
     * @throws CreateNotFoundException
     */
    public function changeStatusAction(Request $request)
    {
        $method = 'get';
        $entityManager = $this->getDoctrine()->getManager();
        //get status and Status entity
        $status = $entityManager->getRepository('OpitNotesStatusBundle:Status')
            ->find($request->attributes->get('status'));
        //get token and Token entity
        $token = $entityManager->getRepository('OpitNotesLeaveBundle:Token')
            ->findOneBy(array('token' => $request->attributes->get('token')));

        // if $token is not an instance of Token entity throw an exception
        if (false === ($token instanceof Token)) {
            throw $this->createNotFoundException('Security token is not valid. Status cannot be updated.');
        }

        $leaveRequest = $entityManager
            ->getRepository('OpitNotesLeaveBundle:leaveRequest')
            ->find($token->getLeaveId());
        if (null === $leaveRequest) {
            throw $this->createNotFoundException('Missing leave request.');
        }

        if ($request->isMethod('POST')) {
            $method = 'post';

            if (null === $leaveRequest) {
                throw $this->createNotFoundException('Missing leave request.');
            }

            $entityManager->remove($token);
            $entityManager->flush();
            $this->get('opit.manager.leave_status_manager')->addStatus($leaveRequest, $status->getId());
        }

        return $this->render(
            'OpitNotesLeaveBundle:Shared:updateStatus.html.twig',
            array('status' => strtolower($status->getName()), 'method' => $method)
        );
    }

    /**
     * Retrieves and displays leave request status history
     *
     * @Route("/secured/leave/states/history/{id}", name="OpitNotesLeaveBundle_status_history", requirements={"id"="\d+"})
     * @Method({"POST"})
     * @Template()
     */
    public function showStatusHistoryAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $leaveRequest = $entityManager
            ->getRepository('OpitNotesLeaveBundle:LeaveRequest')
            ->find($id);

        $leaveRequestStates = $entityManager
                ->getRepository('OpitNotesLeaveBundle:StatesLeaveRequests')
                ->findByLeaveRequest($leaveRequest, array('created' => 'DESC'));

        return $this->render(
            'OpitNotesCoreBundle:Shared:statusHistory.html.twig',
            array(
                'elements' => array(
                    'tr' => array(
                        'title' => 'Leave Request',
                        'collection' => $leaveRequestStates
                    )
                )
            )
        );
    }
}
