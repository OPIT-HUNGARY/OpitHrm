<?php
namespace Opit\Notes\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\Notes\TravelBundle\Entity\TravelExpense;
use Opit\Notes\TravelBundle\Model\TravelExpenseExtension;
use Opit\Notes\TravelBundle\Form\ExpenseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use TCPDF;

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
        
        if (false === $this->get('security.context')->isGranted('ROLE_ADMIN') &&
            false === $securityContext->isGranted('VIEW', $travelRequest)) {
            throw $this->createNotFoundException('You are not permitted to view or edit the travel expense!');
        }
        
        $travelRequestId = $travelRequest->getTravelRequestId();
        $trArrivalDate = $travelRequest->getArrivalDate();
        $trDepartureDate = $travelRequest->getDepartureDate();
        
        $travelExpense = ($isNewTravelExpense) ? $this->getTravelExpense($travelExpenseId) : new TravelExpense();
        
        // Get rates
        $exchService = $this->container->get('opit.service.exchange_rates');
        $rates = $exchService->getRatesByDate($this->getMidRate($travelExpense->getId()));
        
        // te = Travel Expense
        $travelExpenseStates = array();
        $isEditLocked = array();
        $isStatusLocked = array();
        $statusManager = $this->get('opit.manager.status_manager');
        
        // get travel expense current status
        $currentStatus = $statusManager->getCurrentStatus($travelExpense);
        $currentStatusName = $currentStatus->getName();
        $currentStatusId = $currentStatus->getId();
        
        // set availabilty(edit, change status) for travel expense
        $teAvailability =
            $this->setTEAvailability($travelRequest->getGeneralManager()->getId(), $currentUser->getId(), $currentStatusName);
        $isEditLocked = $teAvailability['isEditLocked'];
        $isStatusLocked = $teAvailability['isStatusLocked'];
        
        if (false === $isNewTravelExpense) {
            $travelExpense->setUser($currentUser);
        } else {
            //if status is locked do not load all selectable states for expense
            if (false === $isStatusLocked) {
                $travelExpenseStates = $statusManager->getNextStates($currentStatus);
            }
            
            $trArrivalDate = $travelExpense->getArrivalDateTime();
            $trDepartureDate = $travelExpense->getDepartureDateTime();
        }
        
        // set current status for travel expense
        $travelExpenseStates[$currentStatusId] = $currentStatusName;
        
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
                
                $travelExpense = TravelExpenseExtension::calculateAdvances($travelExpense);
                
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
            'trId' => $travelRequestId,
            'travelExpenseStates' => $travelExpenseStates,
            'isEditLocked' => $isEditLocked,
            'isStatusLocked' => $isStatusLocked,
            'rates' => json_encode($rates)
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
        
        $travelExpense = TravelExpenseExtension::calculateAdvances($travelExpense);
        
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
        $detailsOfPerDiem = TravelExpenseExtension::calculatePerDiem(
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
            $page = $this->getTravelExpensePage($travelExpenseId);

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('NOTES');
            $pdf->SetTitle('Travel Expense');
            $pdf->SetSubject('Travel Expense details');
            $pdf->SetKeywords('travel, expense, notes');
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->SetFont('', '', 12);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->AddPage();
            $pdf->writeHTML($page, true, true, false, '');
            $pdf->lastPage();

            $filename = 'test.pdf';

            $pdf->Output($filename, 'D');
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
         
        $statusManager = $this->get('opit.manager.status_manager');
        $statusManager->addStatus($travelExpense, $statusId);
        
        return new JsonResponse();
    }
    
    /**
     * Returns viewTravelExpense page rendered
     * 
     * @param integer $travelExpenseId
     * @return mixed TravelExpense or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getTravelExpensePage($travelExpenseId)
    {
        $currencyConfig = $this->container->getParameter('exchange_rate');
        $exchManager = $this->container->get('opit.service.exchange_rates');
        
        $travelExpense = $this->getTravelExpense($travelExpenseId);
        $travelRequest = $travelExpense->getTravelRequest();
        $generalManager = $travelRequest->getGeneralManager()->getEmployeeName();
        $employee = $travelRequest->getUser()->getEmployeeName();
        $dateTimeNow = date("Y-m-d H:i");
        $expensesPaidbyCompany = 0;
        $expensesPaidByEmployee = 0;

        $departureDateTime = new \DateTime($travelExpense->getDepartureDateTime()->format('Y-m-d H:i:s'));
        $arrivalDateTime = new \DateTime($travelExpense->getArrivalDateTime()->format('Y-m-d H:i:s'));
        $perDiem = TravelExpenseExtension::calculatePerDiem(
            $this->getDoctrine()->getManager(),
            $arrivalDateTime,
            $departureDateTime
        );

        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $expensesPaidbyCompany += $exchManager->convertCurrency(
                $companyPaidExpenses->getCurrency()->getCode(),
                $currencyConfig['default_currency'],
                $companyPaidExpenses->getAmount()
            );
        }

        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $expensesPaidByEmployee += $exchManager->convertCurrency(
                $userPaidExpenses->getCurrency()->getCode(),
                $currencyConfig['default_currency'],
                $userPaidExpenses->getAmount()
            );
        }


        $page = $this->render(
            'OpitNotesTravelBundle:Expense:viewTravelExpense.html.twig',
            array(
                'travelExpense' => $travelExpense, 'print' => true, 'generalManager' => $generalManager,
                'employee' => $employee, 'datetime' => $dateTimeNow,
                'trId' => $travelRequest->getTravelRequestId(),
                'perDiem' => $perDiem,
                'expensesPaidByCompany' => $expensesPaidbyCompany,
                'expensesPaidByEmployee' => $expensesPaidByEmployee,
                'currencyFormat' => $currencyConfig['currency_format'],
                'midRate' => $this->getMidRate($travelExpenseId)
            )
        );

        return $page;
    }
    
    /**
     * Return travel expense entity
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
    
    /**
     * Method to to enable or disable travel expense
     * 
     * @param integer $travelRequestGM
     * @param integer $currentUser
     * @param string $currentStatusName
     * @return boolean $trAvailability
     */
    protected function setTEAvailability($travelRequestGM, $currentUser, $currentStatusName)
    {
        $teAvailability = array();
        if (true === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $teAvailability['isEditLocked'] = false;
            $teAvailability['isStatusLocked'] = false;
        } elseif ($travelRequestGM === $currentUser) {
            $teAvailability['isEditLocked'] = true;
            if ('Created' === $currentStatusName && 'Revise' === $currentStatusName) {
                $teAvailability['isStatusLocked'] = true;
            } else {
                $teAvailability['isStatusLocked'] = false;
            }
        } else {
            if ('Created' === $currentStatusName || 'Revise' === $currentStatusName) {
                $teAvailability['isEditLocked'] = false;
                $teAvailability['isStatusLocked'] = false;
            } else {
                $teAvailability['isEditLocked'] = true;
                $teAvailability['isStatusLocked'] = true;
            }
        }
        
        return $teAvailability;
    }
    
    /**
     * Get the middle rate.
     * 
     * @todo handle empty rates
     * @param type $travelExpenseId
     * @return type
     */
    protected function getMidRate($travelExpenseId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $lastTEStatus = $entityManager->getRepository('OpitNotesTravelBundle:StatesTravelExpenses')
                                 ->getCurrentStatus($travelExpenseId);
        // Set the midrate of last month
        $midRate = $lastTEStatus ? $lastTEStatus->getCreated() : new \DateTime('today');
        $midRate->setDate($midRate->format('Y'), $midRate->format('m'), 15);
        $midRate->modify('-1 month');
        
        // TODO: handle empty rates.
        
        return $midRate;
    }
}
