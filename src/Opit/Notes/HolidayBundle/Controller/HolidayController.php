<?php

namespace Opit\Notes\HolidayBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\TravelBundle\Helper\Utils;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\HolidayBundle\Form\LeaveRequestType;
use Opit\Notes\HolidayBundle\Entity\LeaveRequest;

class HolidayController extends Controller
{
    /**
     * To add/edit holiday in Notes
     *
     * @Route("/secured/leave/show/{id}", name="OpitNotesTravelBundle_leave_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_USER")
     * @throws CreateNotFoundException
     * @Template()
     */
    public function showLeaveRequestAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $children = new ArrayCollection();
        $leaveRequestId = $request->attributes->get('id');
        $isNewLeaveRequest = 'new' === $leaveRequestId ? true : false;
        $securityContext = $this->container->get('security.context');
        $token = $securityContext->getToken();
        
        if ($isNewLeaveRequest) {
            $employee = $token->getUser()->getEmployee();
            $leaveRequest = new LeaveRequest();
            $leaveRequest->setEmployee($employee);
        } else {
            $leaveRequest = $entityManager->getRepository('OpitNotesHolidayBundle:LeaveRequest')->find($leaveRequestId);
            
            if (null === $leaveRequest) {
                throw $this->createNotFoundException('Missing leave request.');
            }
            
            if ($token->getUser()->getEmployee() !== $leaveRequest->getEmployee() && !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException(
                    'Access denied for leave request ' . $leaveRequest->getLeaveRequestId()
                );
            }
        }
        
        $form = $this->createForm(
            new LeaveRequestType($isNewLeaveRequest),
            $leaveRequest,
            array('em' => $entityManager, 'validation_groups' => array('user'))
        );
        
        if (null !== $leaveRequest) {
            foreach ($leaveRequest->getLeaves() as $leave) {
                $children->add($leave);
            }
        }
                
        if ($request->isMethod("POST")) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                foreach ($children as $child) {
                    if (false === $leaveRequest->getLeaves()->contains($child)) {
                        $child->setLeaveRequest();
                        $entityManager->remove($child);
                    }
                }
                
                $entityManager->persist($leaveRequest);
                $entityManager->flush();
            }
        }
        
        return $this->render(
            'OpitNotesHolidayBundle:Holiday:showLeaveRequest.html.twig',
            array('form' => $form->createView(), 'isNewLeaveRequest' => $isNewLeaveRequest)
        );
    }
}
