<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Opit\Notes\TravelBundle\Form\TravelType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TRDestination;
use Opit\Notes\TravelBundle\Helper\TravelBundleUtils;

use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class TravelController extends Controller
{
    /**
     * @Route("/secured/travel/list", name="OpitNotesTravelBundle_travel_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $travelRequests = $this->getDoctrine()->getRepository('OpitNotesTravelBundle:TravelRequest')->findAll();

        return array("travelRequests" => $travelRequests);
    }

    /**
     * @Route("/secured/travel/search", name="OpitNotesTravelBundle_travel_search")
     * @Template()
     */
    public function searchAction()
    {
        $request = $this->getRequest()->request->all();
        $empty = array_filter($request, function ($value) {
            return !empty($value);
        });

        $travelRequests = null;

        if (array_key_exists('resetForm', $request) || empty($empty)) {
             list($travelRequests) = array_values($this->listAction());
        } else {
            $em = $this->getDoctrine()->getManager();
            $travelRequests = $em->getRepository('OpitNotesTravelBundle:TravelRequest')
                                 ->getTravelRequestsBySearchParams($request);
        }
        return $this->render(
            'OpitNotesTravelBundle:Travel:_list.html.twig',
            array("travelRequests" => $travelRequests)
        );
    }

    /**
     * To generate details form for travel requests
     *
     * @Route("/secured/travel/show/details", name="OpitNotesTravelBundle_travel_show_details")
     * @Template()
     */
    public function showDetailsAction()
    {
        $travelRequest = new TravelRequest();
        $request = $this->getRequest();
        $travelRequestPreview = $request->request->get('preview');
        
        // for creating entities for the travel request preview
        if (null !== $travelRequestPreview) {
            $entityManager = $this->getDoctrine()->getManager();
            $form = $this->createForm(new TravelType(), $travelRequest, array('em' => $entityManager));
            # bind travel request to form and set data to it
            $form->handleRequest($request);
        } else {
            $travelRequest = $this->getTravelRequest();
        }
        
        return array('travelRequest' => $travelRequest);
    }
    
    /**
     * Method to show and edit travel request
     *
     * @Route("/secured/travel/show/{id}", name="OpitNotesTravelBundle_travel_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Template()
     */
    public function showTravelRequestAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->attributes->get('id');
        
        $travelRequest = ("new" == $travelRequestId) ? new TravelRequest() : $this->getTravelRequest($travelRequestId);
        
        // Track current persisted destination objects
        $children = new ArrayCollection();
        
        foreach ($travelRequest->getDestinations() as $destination) {
            $children->add($destination);
        }
        
        foreach ($travelRequest->getAccomodations() as $accomodation) {
            $children->add($accomodation);
        }
        
        $form = $this->createForm(new TravelType(), $travelRequest, array('em' => $entityManager));
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // Persist deleted destinations/accomodations
                $this->removeChildNodes($entityManager, $travelRequest, $children);

                $entityManager->persist($travelRequest);
                $entityManager->flush();
                
                return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
            }
        }
        
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/secured/travel/usersearch", name="OpitNotesTravelBundle_travel_userSearch")
     * @Method({"GET"})
     */
    public function userSearchAction()
    {
        $userNames = array();
        $request = $this->getRequest();
        $term = $request->query->get('term');
        $user = $request->query->get('user');
        $users = $this->getDoctrine()->
                        getRepository('OpitNotesUserBundle:User')->
                        findUserByEmployeeNameUsingLike($term);

        foreach ($users as $user) {
            $userNames[] = array(
                'value'=>$user->getEmployeeName(),
                'label'=>$user->getEmployeeName(),
                'id'=>$user->getId()
            );
        }
        
        return new JsonResponse($userNames);
    }
    
    /**
     * Method to delete one or more travel requests
     *
     * @Route("/secured/travel/delete", name="OpitNotesTravelBundle_travel_delete")
     * @Template()
     * @Method({"POST"})
     */
    public function deleteTravelRequestAction(Request $request)
    {
        $ids = $request->request->get('id');
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ($ids as $id) {
            $entityManager = $this->getDoctrine()->getManager();
            $travelRequest = $this->getTravelRequest($id);

            $entityManager->remove($travelRequest);
        }
        
        $entityManager->flush();
        
        return new JsonResponse('0');
    }
    
    /**
     * Returns a travel request object
     *
     * @param integer $travelRequestId
     * @return mixed  TravelRequest object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getTravelRequest($travelRequestId = null)
    {
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        
        if (null === $travelRequestId) {
            $travelRequestId = $request->request->get('id');
        }
        
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($travelRequestId);
        
        if (!$travelRequest) {
            throw $this->createNotFoundException('Missing travel request for id "' . $travelRequestId . '"');
        }
        
        return $travelRequest;
    }
    
    /**
     * Removes related travel request instances.
     *
     * @param object $entityManager
     * @param object $travelRequest
     * @param ArrayCollection $children
     */
    protected function removeChildNodes(&$entityManager, $travelRequest, $children)
    {
        foreach ($children as $child) {
            $getter = ($child instanceof TRDestination) ? 'getDestinations' : 'getAccomodations';
            if (false === $travelRequest->$getter()->contains($child)) {
                $child->setTravelRequest(null);
                $entityManager->remove($child);
            }
        }
    }
}
