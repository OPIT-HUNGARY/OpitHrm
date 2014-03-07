<?php
namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Form\ExpenseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Opit\Notes\TravelBundle\Entity\Status;

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
     * @Route("/secured/expense/{id}/show/{trId}", name="OpitNotesTravelBundle_expense_show", defaults={"id" = "new"}, requirements={ "id" = "new|\d+", "trId" = "\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function showTravelExpenseAction(Request $request, $trId, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $isNewTravelExpense = "new" !== $id;
        $currentUser = $this->getUser();
        $travelExpenseService = $this->get('opit.model.travel_expense');
        $exchService = $this->container->get('rate.exchange_service');
        $travelRequest = $entityManager->getRepository('OpitNotesTravelBundle:TravelRequest')->find($trId);
        $travelExpense = ($isNewTravelExpense) ? $this->getTravelExpense($id) : new TravelExpense();
        $approvedCosts = $travelExpenseService->getTRCosts($travelRequest);
        // Get rates
        $rates = $exchService->getRatesByDate($travelExpenseService->getMidRate());
        
        // te = Travel Expense
        $travelExpenseStates = array();
        $statusManager = $this->get('opit.manager.status_manager');
        
        // get travel expense current status
        $currentStatus = $statusManager->getCurrentStatus($travelExpense);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();
        
        // set availabilty(edit, change status) for travel expense
        $editRights = $travelExpenseService->setEditRights(
            $travelRequest,
            $currentUser,
            $currentStatus->getId()
        );
        $isEditLocked = $editRights['isEditLocked'];
        $isStatusLocked = $editRights['isStatusLocked'];
        
        if (false === $isNewTravelExpense) {
            $travelExpense->setUser($currentUser);
        } else {
            //if status is locked do not load all selectable states for expense
            if (false === $isStatusLocked) {
                $travelExpenseStates = $statusManager->getNextStates($currentStatus);
            }
        }
        
        // set current status for travel expense
        $travelExpenseStates[$currentStatusId] = $currentStatusName;
        
        $children = $this->get('opit.model.travel_expense')->addChildNodes($travelExpense);
        
        $entityManager->getFilters()->disable('softdeleteable');
        
        $travelExpense->setArrivalDateTime(
            $isNewTravelExpense ? $travelExpense->getArrivalDateTime() : $travelRequest->getArrivalDate()
        );
        $travelExpense->setDepartureDateTime(
            $isNewTravelExpense ? $travelExpense->getDepartureDateTime() : $travelRequest->getDepartureDate()
        );

        $form =
            $this->handleForm($isNewTravelExpense, $travelRequest, $travelExpense, $entityManager, $children, $request);
        
        if (true === $form) {
            return $this->redirect($this->generateUrl('OpitNotesTravelBundle_travel_list'));
        }
        
        return array(
            'form' => $form->createView(),
            'travelExpense' => $travelExpense,
            'trId' => $travelRequest->getTravelRequestId(),
            'travelExpenseStates' => $travelExpenseStates,
            'isEditLocked' => $isEditLocked,
            'isStatusLocked' => $isStatusLocked,
            'rates' => json_encode($rates),
            'approvedCostsEUR' => ceil($approvedCosts['EUR']),
            'approvedCostsHUF' => ceil($approvedCosts['HUF'])
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
        
        return array('travelExpense' => $this->get('opit.model.travel_expense')->calculateAdvances($travelExpense));
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
     * Method to calculate and return per diem values
     *
     * @Route("/secured/expense/perdiem", name="OpitNotesTravelBundle_expense_perdiem")
     * @Template()
     * @Method({"POST"})
     */
    public function fetchPerDiemAction(Request $request)
    {
        $departureDateTime = new \DateTime($request->request->get('departure'));
        $arrivalDateTime = new \DateTime($request->request->get('arrival'));
        $detailsOfPerDiem = $this->get('opit.model.travel_expense')->calculatePerDiem(
            $this->getDoctrine()->getManager(),
            $arrivalDateTime,
            $departureDateTime
        );
        
        return new JsonResponse($detailsOfPerDiem);
    }
    
    /**
     * Method to fetch per diem values from database
     *
     * @Route("/secured/expense/perdiemvalues", name="OpitNotesTravelBundle_expense_perdiemvalues")
     * @Template()
     * @Method({"POST"})
     */
    public function fetchPerDiemValuesAction()
    {
        
        $entityManager = $this->getDoctrine()->getManager();
        $perDiemAmounts = $entityManager->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAll();
        $values = array();
        foreach ($perDiemAmounts as $value) {
            $values[$value->getHours()] = $value->getAmount();
        }
        return new JsonResponse($values);
    }
    
    /**
     * Method to view travel expense
     *
     * @Route("/secured/expense/view/{id}", name="OpitNotesTravelBundle_expense_view", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Template()
     */
    public function viewTravelExpenseAction(Request $request)
    {
        $travelExpenseId = $request->attributes->get('id');
        $page = $this->getTravelExpensePage($travelExpenseId);
        
        return $page;
    }
    
    /**
     * Method to export expense to pdf
     *
     * @Route("/secured/expense/export/{id}", name="OpitNotesTravelBundle_expense_export", defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Template()
     */
    public function exportExpenseToPDFAction(Request $request)
    {
        $travelExpenseId = $request->attributes->get('id');
        if ('new' !== $travelExpenseId) {
            $pdfContent = $this->getTravelExpensePage($travelExpenseId);
            $pdf = $this->get('opit.manager.pdf_manager');
            $pdf->exportToPdf(
                $pdfContent,
                'test.pdf',
                'NOTES',
                'Travel Expense',
                'Travel Expense details',
                array('travel', 'expense', 'notes'),
                12,
                array()
            );
        }
    }
    
    /**
     * Method to change state of travel expense
     *
     * @Route("/secured/expense/state/", name="OpitNotesTravelBundle_expense_state")
     * @Template()
     */
    public function changeTravelExpenseStateAction(Request $request)
    {
        $statusId = $request->request->get('statusId');
        $travelExpenseId = $request->request->get('travelExpenseId');
        $entityManager = $this->getDoctrine()->getManager();
        $travelExpense = $entityManager->getRepository('OpitNotesTravelBundle:TravelExpense')->find($travelExpenseId);
         
        $this->get('opit.manager.status_manager')->addStatus($travelExpense, $statusId);
        
        return new JsonResponse();
    }
    
    /**
     * Returns viewTravelExpense page rendered
     * 
     * @param integer $travelExpenseId
     * @return mixed \Opit\Notes\TravelBundle\Entity\TravelRequest or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getTravelExpensePage($travelExpenseId)
    {
        $currencyConfig = $this->container->getParameter('exchange_rate');
        $travelExpenseService = $this->get('opit.model.travel_expense');
        
        $travelExpense = $this->getTravelExpense($travelExpenseId);
        $travelRequest = $travelExpense->getTravelRequest();
        $generalManager = $travelRequest->getGeneralManager()->getEmployeeName();
        $employee = $travelRequest->getUser()->getEmployeeName();
        $dateTimeNow = date("Y-m-d H:i");

        $departureDateTime = new \DateTime($travelExpense->getDepartureDateTime()->format('Y-m-d H:i:s'));
        $arrivalDateTime = new \DateTime($travelExpense->getArrivalDateTime()->format('Y-m-d H:i:s'));
        $perDiem =  $travelExpenseService->calculatePerDiem(
            $this->getDoctrine()->getManager(),
            $arrivalDateTime,
            $departureDateTime
        );

        $travelExpenseExpenses = $travelExpenseService->sumExpenses($travelExpense);
        $sumOfCostsByCurrencies = $travelExpenseService->getCostsByCurrencies($travelExpense);
        $midRate = $travelExpenseService->getMidRate();
        
        return $this->render(
            'OpitNotesTravelBundle:Expense:viewTravelExpense.html.twig',
            array(
                'travelExpense' => $travelExpense, 'print' => true, 'generalManager' => $generalManager,
                'employee' => $employee, 'datetime' => $dateTimeNow,
                'trId' => $travelRequest->getTravelRequestId(),
                'perDiem' => $perDiem,
                'expensesPaidByCompany' => $travelExpenseExpenses['companyPaidExpenses'],
                'expensesPaidByEmployee' => $travelExpenseExpenses['employeePaidExpenses'],
                'currencyFormat' => $currencyConfig['currency_format'],
                'midRate' => $midRate,
                'companyPaidExpenses' => $sumOfCostsByCurrencies['companyPaidExpenses'],
                'employeePaidExpenses' => $sumOfCostsByCurrencies['employeePaidExpenses'],
                'rates' => $this->container->get('rate.exchange_service')
                               ->getRatesByDate($midRate)
            )
        );
    }
    
    /**
     * Return travel expense entity
     * 
     * @param integer $travelExpenseId
     * @return mixed \Opit\Notes\TravelBundle\Entity\TravelExpense or null
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
    
    /**
     * 
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @throws CreateNotFoundException
     */
    protected function isAccessGranted($travelRequest)
    {
        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted('ROLE_ADMIN') &&
            false === $securityContext->isGranted('VIEW', $travelRequest)) {
            throw $this->createNotFoundException('You are not permitted to view or edit the travel expense!');
        }
        
        if (null === $travelRequest) {
            throw $this->createNotFoundException('No travel request was found with the given id!');
        }
    }

    /**
     * Method to create and/or save travelExpense
     * 
     * @param boolean $isNewTravelExpense
     * @param \Opit\Notes\TravelBundle\Entity\TravelRequest $travelRequest
     * @param \Opit\Notes\TravelBundle\Entity\TravelExpense $travelExpense
     * @param EntityManager $entityManager
     * @param ArrayCollection $children
     * @param Request $request
     * @return form
     */
    protected function handleForm($isNewTravelExpense, $travelRequest, $travelExpense, EntityManager $entityManager, $children, $request)
    {
        $form = $this->createForm(
            new ExpenseType($this->get('security.context')->isGranted('ROLE_ADMIN'), $isNewTravelExpense),
            $travelExpense,
            array('em' => $entityManager)
        );
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $statusManager = $this->get('opit.manager.status_manager');
                $isNew = $travelExpense->getId();
                $travelExpense = $this->get('opit.model.travel_expense')->calculateAdvances($travelExpense);
                
                $this->get('opit.model.travel_expense')->removeChildNodes($entityManager, $travelExpense, $children);
                $travelExpense->setTravelRequest($travelRequest);
                $entityManager->persist($travelExpense);
                $entityManager->flush();
                
                // Create initial state for new travel expense.
                if (null === $isNew) {
                    $statusManager->forceStatus(Status::CREATED, $travelExpense, $this->getUser());
                    $statusManager->addStatus($travelExpense, Status::CREATED);
                }
                
                return true;
            }
        }
        
        return $form;
    }
}
