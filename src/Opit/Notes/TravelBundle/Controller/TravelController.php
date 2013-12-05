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

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 */
class TravelController extends Controller
{
    protected $jsRoutes;

    /**
     * @Route("/secured/travel/list", name="OpitNotesTravelBundle_travel_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();
        $travelRequests = $this->getDoctrine()->getRepository('OpitNotesTravelBundle:TravelRequest')->findAll();

        // urls for js scripts
        $this->jsRoutes = $this->generateJsRoutes();

        return array("travelRequests" => $travelRequests, 'urls' => $this->jsRoutes);
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
        $request = $this->getRequest();
        $trId =  (integer) $request->request->get('trId');
        $travelRequest = null;

        if ($trId > 0) {
            $em = $this->getDoctrine()->getManager();
            $travelRequest = $em->getRepository('OpitNotesTravelBundle:TravelRequest')->find($trId);
        }
        return array('travelRequest' => $travelRequest);
    }

    /**
     * Generates notes routes for use in js scripts
     *
     * @return array Genrated notes routes collection
     */
    protected function generateJsRoutes()
    {
        $router = $this->container->get('router');

        $js_routes = array();
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            if (strpos($name, 'OpitNotesTravelBundle') !== false) {
                $js_routes[$name] = $this->generateUrl($name);
            }
        }

        return $js_routes;
    }
    
    /**
     * @Route("/secured/travel/show/{id}", name="OpitNotesTravelBundle_travel_show", defaults={"id" = 0})
     * @Template()
     */
    public function showTravelRequestAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $travelRequestId = $request->attributes->get('id');
        
        if (null === ($travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($travelRequestId))) {
            $travelRequest = new TravelRequest();
        }
        
        if (!$travelRequest) {
            throw $this->createNotFoundException('Missing travel request for id "' . $travelRequestId . '"');
        }
        
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
            }
        }
        
        return array('form' => $form->createView());
    }
    
    protected function removeChildNodes(&$entityManager, $travelRequest, $children)
    {
        foreach ($children as $child) {
            $getter = ($child instanceof TRDestination) ? 'getDestinations' : 'getAccomodations';
            if (false === $travelRequest->$getter()->contains($child)) {
                $child->setTravelRequest(null);
                $entityManager->persist($child);
                $entityManager->remove($child);
            }
        }
    }
    
    /**
     * @Route("/secured/travel/search", name="OpitNotesTravelBundle_travel_userSearch")
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
}
