<?php
namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Entity\TravelRequest;
use Opit\Notes\TravelBundle\Entity\TEPerDiem;
use Opit\Notes\TravelBundle\Form\ExpenseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Description of ExpenseController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Opit
 * @subpackage Notes
 */
class ExpenseController extends Controller
{
    /**
     * @Route("/secured/expense/list", name="OpitNotesTravelBundle_expense_list")
     * @Template()
     */
    public function listAction()
    {
        //show error message to disable use of this function
        throw $this->createNotFoundException('Page not found!');
        
        $entityManager = $this->getDoctrine()->getManager();
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');
        $securityContext = $this->get('security.context');

        $travelExpenses = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense')->findAll();
        $allowedTEs = new ArrayCollection();

        if (!$securityContext->isGranted('ROLE_ADMIN')) {
            foreach ($travelExpenses as $trExpense) {
                if (true === $securityContext->isGranted('VIEW', $trExpense)) {
                    $allowedTEs->add($trExpense);
                }
            }
        } else {
            $allowedTEs = $travelExpenses;
        }
        return array("travelExpenses" => $allowedTEs);
    }

    /**
     * @Route("/secured/expense/search", name="OpitNotesTravelBundle_expense_search")
     * @Template()
     */
    public function searchAction()
    {
        //show error message to disable use of this function
        throw $this->createNotFoundException('Page not found!');
        
        $request = $this->getRequest()->request->all();
        $empty = array_filter($request, function ($value) {
            return !empty($value);
        });

        $travelExpenses = null;

        if (array_key_exists('resetForm', $request) || empty($empty)) {
             list($travelExpenses) = array_values($this->listAction());
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            $travelExpenses = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense')
                                 ->getTravelExpensesBySearchParams($request);
        }
        return $this->render(
            'OpitNotesTravelBundle:Expense:_list.html.twig',
            array("travelExpenses" => $travelExpenses)
        );
    }
        /**
     * Method to show and edit travel expense
     *
     * @Route("/secured/expense/show/{id}", name="OpitNotesTravelBundle_expense_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Template()
     */
    public function showTravelExpenseAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $travelExpenseId = $request->attributes->get('id');
        $trId = $request->query->get('tr');
        $isNewTravelExpense = "new" !== $travelExpenseId;
        $securityContext = $this->get('security.context');
        $currentUser = $securityContext->getToken()->getUser();
        
        if (null === $trId) {
            throw $this->createNotFoundException('No travel request was found with the given id!');
        }
        
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($trId);

        if (null === $travelRequest) {
            //show error message if no travel request was found with the given id
            throw $this->createNotFoundException('No travel request was found with the given id!');
        }
        
        $travelRequestId = $travelRequest->getTravelRequestId();
        $trArrivalDate = $travelRequest->getArrivalDate();
        $trDepartureDate = $travelRequest->getDepartureDate();
        
        $travelExpense = ($isNewTravelExpense) ? $this->getTravelExpense($travelExpenseId) : new TravelExpense();
        
        if (false === $isNewTravelExpense) {
            $travelExpense->setUser($currentUser);
        } else {
            $trArrivalDate = $travelExpense->getArrivalDateTime();
            $trDepartureDate = $travelExpense->getDepartureDateTime();
        }
        
        $children = new ArrayCollection();
        
        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $children->add($companyPaidExpenses);
        }
        
        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $children->add($userPaidExpenses);
        }
        
        $entityManager->getFilters()->disable('softdeleteable');
        
        $travelExpense->setArrivalDateTime($trArrivalDate);
        $travelExpense->setDepartureDateTime($trDepartureDate);
        
        $form = $this->createForm(
            new ExpenseType($this->get('security.context')->isGranted('ROLE_ADMIN'), $isNewTravelExpense),
            $travelExpense,
            array('em' => $entityManager)
        );
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $this->removeChildNodes($entityManager, $travelExpense, $children);
                
                $travelExpense->setTravelRequest($travelRequest);
                
                $entityManager->persist($travelExpense);
                $entityManager->flush();
                
                return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
            }
        }
        
        return array(
            'form' => $form->createView(),
            'travelExpense' => $travelExpense,
            'trId' => $travelRequestId
            );
    }
    
    /**
     * To generate details form for travel expenses
     *
     * @Route("/secured/expense/show/details", name="OpitNotesTravelBundle_expense_show_details")
     * @Template()
     */
    public function showDetailsAction()
    {
        $travelExpense = new TravelExpense();
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        $travelExpensePreview = $request->request->get('preview');
        
        if (null !== $travelExpensePreview) {
            $form = $this->createForm(new ExpenseType(), $travelExpense, array('em' => $entityManager));
            $form->handleRequest($request);
        } else {
            $travelExpense = $this->getTravelExpense();
        }
        
        return array('travelExpense' => $travelExpense);
    }
    
    /**
     * Method to delete one or more travel expense
     *
     * @Route("/secured/expense/delete", name="OpitNotesTravelBundle_expense_delete")
     * @Template()
     * @Method({"POST"})
     */
    public function deleteTravelExpenseAction(Request $request)
    {
        $securityContext = $this->get('security.context');
        $ids = $request->request->get('id');
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        
        foreach ($ids as $id) {
            $entityManager = $this->getDoctrine()->getManager();
            $travelExpense = $this->getTravelExpense($id);
            
            // Ensure that no travel requests without permission get deleted
            if ($securityContext->isGranted('ROLE_ADMIN') ||
                true === $securityContext->isGranted('DELETE', $travelExpense)) {
                $entityManager->remove($travelExpense);
            }
        }
        
        $entityManager->flush();
        
        return new JsonResponse('0');
    }
    
    /**
     * Method to fetch per diem from database
     *
     * @Route("/secured/expense/perdiem", name="OpitNotesTravelBundle_expense_perdiem")
     * @Template()
     * @Method({"POST"})
     */
    public function fetchPerDiemAction(Request $request)
    {
        
        $entityManager = $this->getDoctrine()->getManager();
        $perDiemAmount = 0;
        $daysBetweenArrivalDeparture = 0;
        $totalTravelHoursOnSameDay = 0;
        $daysBetweenPerDiem = 0;
        
        $departureDayTravelHours = 0;
        $departureDayPerDiem = 0;
        $departureDateTime = new \DateTime($request->request->get('departure'));
        $departureTimeHour = intval($departureDateTime->format('H'));
        $departureDay = intval($departureDateTime->format('d'));
        $departureDate = $departureDateTime->format('Y-m-d');
        
        $arrivalDayTravelHours = 0;
        $arrivalDayPerDiem = 0;
        $arrivalDateTime = new \DateTime($request->request->get('arrival'));
        $arrivalTimeHour = intval($arrivalDateTime->format('H'));
        $arrivalDay = intval($arrivalDateTime->format('d'));
        $arrivalDate = $arrivalDateTime->format('Y-m-d');
        
        if ($departureDate !== $arrivalDate) {
            
            while ($departureTimeHour < 24) {
                $departureTimeHour++;
                $departureDayTravelHours++;
            }

            $departureDayPerDiem =
                $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAmountToPay(
                    $departureDayTravelHours
                );
            
            $perDiemAmount += $departureDayPerDiem;
            
            while ($arrivalTimeHour > 0) {
                $arrivalTimeHour--;
                $arrivalDayTravelHours++;
            }

            $arrivalDayPerDiem =
                $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay($arrivalDayTravelHours);
            
            $perDiemAmount += $arrivalDayPerDiem;
            
            $daysBetweenArrivalDeparture = ($arrivalDay - $departureDay) - 1;
            $daysBetweenPerDiem =
                ($entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay(24)*$daysBetweenArrivalDeparture);
            
            $perDiemAmount += $daysBetweenPerDiem;
        } else {
            $totalTravelHoursOnSameDay = 0;
            while ($departureTimeHour < $arrivalTimeHour) {
                $departureTimeHour++;
                $totalTravelHoursOnSameDay++;
            }
            $perDiemAmount +=
                $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')
                ->findAmountToPay($totalTravelHoursOnSameDay);
        }

        $detailsOfPerDiem = array(
            'totalTravelHoursOnSameDay' => $totalTravelHoursOnSameDay,
            'departureHours' => $departureDayTravelHours,
            'departurePerDiem' => $departureDayPerDiem,
            'arrivalHours' => $arrivalDayTravelHours,
            'arrivalPerDiem' => $arrivalDayPerDiem,
            'daysBetween' => $daysBetweenArrivalDeparture,
            'daysBetweenPerDiem' => $daysBetweenPerDiem,
            'totalPerDiem' => $perDiemAmount
        );
        
        return new JsonResponse($detailsOfPerDiem);
    }
    
    /**
     * Method to fetch per diem from database
     *
     * @Route("/secured/expense/perdiemvalues", name="OpitNotesTravelBundle_expense_perdiemvalues")
     * @Template()
     * @Method({"POST"})
     */
    public function fetchPerDiemValuesAction(Request $request)
    {
        
        $entityManager = $this->getDoctrine()->getManager();
        $perDiemAmounts = $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAll();
        $values = array();
        foreach ($perDiemAmounts as $key => $value) {
            $values[$value->getHours()] = $value->getAmmount();
        }
        return new JsonResponse($values);
    }
    
    /**
     * 
     * @param integer $travelExpenseId
     * @return mixed TravelExpense or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getTravelExpense($travelExpenseId = null)
    {
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        
        if (null === $travelExpenseId) {
            $travelExpenseId = $request->request->get('id');
        }
        
        $travelExpense = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense')->find($travelExpenseId);
        
        if (!$travelExpense) {
            throw $this->createNotFoundException('Missing travel expense for id "' . $travelExpenseId . '"');
        }
        
        return $travelExpense;
    }
    
    protected function removeChildNodes(&$entityManager, $travelExpense, $children)
    {
        foreach ($children as $child) {
            $getter =
                (strstr(get_class($child), 'TEUserPaidExpense')) ? 'getUserPaidExpenses' : 'getCompanyPaidExpenses';
            
            if (false === $travelExpense->$getter()->contains($child)) {
                $child->setTravelExpense(null);
                $entityManager->remove($child);
            }
        }
    }
}
