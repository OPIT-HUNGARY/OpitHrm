<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\TravelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Opit\OpitHrm\TravelBundle\Entity\TravelExpense;
use Opit\OpitHrm\TravelBundle\Form\ExpenseType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManager;
use Opit\OpitHrm\StatusBundle\Entity\Status;

/**
 * Description of ExpenseController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 */
class ExpenseController extends Controller
{
    /**
     * Method to list travel expenses
     *
     * @Route("/secured/expense/list", name="OpitOpitHrmTravelBundle_expense_list")
     * @Template()
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function listAction()
    {
        //show error message to disable use of this function
        throw $this->createNotFoundException('Page not found!');

        $entityManager = $this->getDoctrine()->getManager();
        // Disable softdeleteable filter for user entity to allow persistence
        $entityManager->getFilters()->disable('softdeleteable');
        $securityContext = $this->get('security.context');

        $travelExpenses = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelExpense')->findAll();
        $allowedTEs = new ArrayCollection();

        if (!$securityContext->isGranted('ROLE_ADMIN')) {
            foreach ($travelExpenses as $trExpense) {
                if ($trExpense->getTravelRequest()->getUser()->getId() === $securityContext->getToken()->getUser()->getId()) {
                    $allowedTEs->add($trExpense);
                }
            }
        } else {
            $allowedTEs = $travelExpenses;
        }
        return array("travelExpenses" => $allowedTEs);
    }

    /**
     * Method to search on travel expenses
     *
     * @Route("/secured/expense/search", name="OpitOpitHrmTravelBundle_expense_search")
     * @Template()
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
            $travelExpenses = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelExpense')
                                 ->getTravelExpensesBySearchParams($request);
        }
        return $this->render(
            'OpitOpitHrmTravelBundle:Expense:_list.html.twig',
            array("travelExpenses" => $travelExpenses)
        );
    }

    /**
     * Method to show and edit travel expense
     *
     * @Route("/secured/expense/{id}/show/{trId}/{forApproval}", name="OpitOpitHrmTravelBundle_expense_show",
     *   defaults={"id" = "new", "forApproval" = "0"},
     *   requirements={ "id" = "new|\d+", "trId" = "\d+", "forApproval" = "\d+"})
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function showTravelExpenseAction(Request $request, $trId, $id, $forApproval)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $isNewTravelExpense = "new" !== $id;
        $currentUser = $this->getUser();
        $travelExpenseService = $this->get('opit.model.travel_expense');
        $exchService = $this->container->get('opit.service.exchange_rates.default');
        $travelRequest = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')->find($trId);
        $travelExpense = ($isNewTravelExpense) ? $this->getTravelExpense($id) : new TravelExpense();
        $approvedCosts = $this->get('opit.model.travel_request')->getTRCosts($travelRequest);
        // Get rates
        $rates = $exchService->getRatesByDate($travelExpenseService->getConversionDate($travelExpense));
        $forApproval = (bool) $forApproval;

        // te = Travel Expense
        $travelExpenseStates = array();
        $statusManager = $this->get('opit.manager.travel_expense_status_manager');

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

        $form = $this->handleForm(
            $isNewTravelExpense,
            $travelRequest,
            $travelExpense,
            $entityManager,
            $children,
            $request
        );

        if ($forApproval) {
            $this->changeExpenseState($travelExpense->getId(), Status::FOR_APPROVAL);
        }

        if (true === $form) {
            return $this->redirect($this->generateUrl('OpitOpitHrmTravelBundle_travel_list'));
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
     * @Route("/secured/expense/show/details/{trId}", name="OpitOpitHrmTravelBundle_expense_show_details",
     *   requirements={ "id" = "\d+"})
     * @Template()
     */
    public function showDetailsAction($trId)
    {
        $travelExpense = new TravelExpense();
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        $travelExpensePreview = $request->request->get('preview');
        $travelRequest = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')->find($trId);

        if (null !== $travelExpensePreview) {
            $form = $this->createForm(
                new ExpenseType($travelRequest->getUser()->getEmployee()),
                $travelExpense,
                array('em' => $entityManager)
            );
            $form->handleRequest($request);
        } else {
            $travelExpense = $this->getTravelExpense();
        }

        $currencyCongif = $this->container->getParameter('currency_config');

        return array(
            'travelExpense' => $this->get('opit.model.travel_expense')->calculateAdvances($travelExpense),
            'currencyFormat' => $currencyCongif['currency_format']
        );
    }

    /**
     * Method to delete one or more travel expense
     *
     * @Route("/secured/expense/delete", name="OpitOpitHrmTravelBundle_expense_delete")
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
            if ($travelExpense->getTravelRequest()->getUser()->getId() === $securityContext->getToken()->getUser()->getId() || $securityContext->isGranted('ROLE_ADMIN')) {
                $entityManager->remove($travelExpense);
            }
        }

        $entityManager->flush();

        return new JsonResponse('0');
    }

    /**
     * Method to calculate and return per diem values
     *
     * @Route("/secured/expense/perdiem", name="OpitOpitHrmTravelBundle_expense_perdiem")
     * @Template()
     * @Method({"POST"})
     */
    public function fetchPerDiemAction(Request $request)
    {
        $departureDateTime = new \DateTime($request->request->get('departure'));
        $arrivalDateTime = new \DateTime($request->request->get('arrival'));
        $detailsOfPerDiem = $this->get('opit.model.travel_expense')->calculatePerDiem(
            $arrivalDateTime,
            $departureDateTime
        );

        return new JsonResponse($detailsOfPerDiem);
    }

    /**
     * Method to fetch per diem values from database
     *
     * @Route("/secured/expense/perdiemvalues", name="OpitOpitHrmTravelBundle_expense_perdiemvalues")
     * @Template()
     * @Method({"POST"})
     */
    public function fetchPerDiemValuesAction()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $perDiemAmounts = $entityManager->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')->findAll();
        $values = array();
        foreach ($perDiemAmounts as $value) {
            $values[$value->getHours()] = $value->getAmount();
        }
        return new JsonResponse($values);
    }

    /**
     * Method to view travel expense
     *
     * @Route("/secured/expense/view/{id}", name="OpitOpitHrmTravelBundle_expense_view",
     *   defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
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
     * @Route("/secured/expense/export/{id}", name="OpitOpitHrmTravelBundle_expense_export",
     *   defaults={"id" = "new"}, requirements={ "id" = "new|\d+"})
     * @Template()
     */
    public function exportExpenseToPDFAction(Request $request)
    {
        $travelExpenseId = $request->attributes->get('id');
        if ('new' !== $travelExpenseId) {
            $travelExpense = $this->getTravelExpense($travelExpenseId);
            $travelRequest = $travelExpense->getTravelRequest();
            $pdfFileName = $travelRequest->getTravelRequestId() . '_Travel_Expense_Report.pdf';
            $pdfContent = $this->getTravelExpensePage($travelExpenseId, 'export')->getContent();
            $pdf = $this->get('opit.manager.pdf_manager');
            $pdf->exportToPdf(
                $pdfContent,
                $pdfFileName,
                'OPIT-HRM',
                'Travel Expense',
                'Travel Expense details',
                array('travel', 'expense', 'opithrm'),
                12,
                array()
            );
        }

        return new JsonResponse();
    }

    /**
     * Method to change state of travel expense
     *
     * @Route("/secured/expense/state/", name="OpitOpitHrmTravelBundle_expense_state")
     * @Template()
     */
    public function changeTravelExpenseStateAction(Request $request)
    {
        $data = $request->request->get('status');
        // Set comment content or null
        $comment = isset($data['comment']) && $data['comment'] ? $data['comment'] : null;

        $this->changeExpenseState($data['foreignId'], $data['id'], $comment);

        return new JsonResponse();
    }

    /**
     * Sending email to payroll about approved travel expense
     *
     * @Route("/secured/expense/send/mail/payroll", name="OpitOpitHrmTravelBundle_expense_send_mail_payroll")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function sendMailToPayroll(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $applicationName = $this->container->getParameter('application_name');
        $currencyConfig = $this->container->getParameter('currency_config');

        // Find the travel request.
        $trId = (integer) $request->request->get('tr_id');
        $travelRequest = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelRequest')->find($trId);
        // Get the travel expense.
        $travelExpense = $travelRequest->getTravelExpense();
        // Find the payroll user.
        $payrollId = (integer) $request->request->get('payroll_id');
        $payroll = $entityManager->getRepository('OpitOpitHrmUserBundle:User')->find($payrollId);

        // Calculating the per diem.
        $travelExpenseService = $this->get('opit.model.travel_expense');
        $departureDateTime = new \DateTime($travelExpense->getDepartureDateTime()->format('Y-m-d H:i:s'));
        $arrivalDateTime = new \DateTime($travelExpense->getArrivalDateTime()->format('Y-m-d H:i:s'));
        $perDiem =  $travelExpenseService->calculatePerDiem(
            $arrivalDateTime,
            $departureDateTime
        );
        $employee = $travelExpense->getUser()->getEmployee();

        // Sending email to payroll.
        $templateVars = array(
            'perDiem' => $perDiem,
            'travelExpense' => $travelExpense,
            'currencyFormat' => $currencyConfig['currency_format'],
            'employee' => $employee
        );
        $emailManager = $this->get('opit.component.email_manager');
        $emailManager->setRecipient($payroll->getEmail());
        $emailManager->setSubject(
            '['.($applicationName !== null && $applicationName != 'OPIT-HRM' ? $applicationName : 'OPIT-HRM')
            .'] - Approved travel expense (' . $travelRequest->getTravelRequestId() . ')'
        );
        $emailManager->setBodyByTemplate(
            'OpitOpitHrmTravelBundle:Mail:travelExpenseForPayroll.html.twig',
            $templateVars
        );
        $emailManager->sendMail();

        return new JsonResponse();
    }

    /**
     * Returns viewTravelExpense page rendered
     *
     * @param integer $travelExpenseId
     * @param string $action name of action
     * @return mixed \Opit\OpitHrm\TravelBundle\Entity\TravelRequest or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getTravelExpensePage($travelExpenseId, $action = null)
    {
        $currencyConfig = $this->container->getParameter('currency_config');
        $travelExpenseService = $this->get('opit.model.travel_expense');
        $statusManager = $this->get('opit.manager.travel_expense_status_manager');

        $travelExpense = $this->getTravelExpense($travelExpenseId);
        $travelRequest = $travelExpense->getTravelRequest();
        $generalManager = $travelRequest->getGeneralManager()->getEmployee()->getEmployeeName();
        $employee = $travelRequest->getUser()->getEmployee()->getEmployeeName();

        $approvedState = $statusManager->getTravelStateByStatusId($travelExpense, Status::APPROVED);
        $approvedDateTime = null === $approvedState ? null : $approvedState->getCreated();

        $departureDateTime = new \DateTime($travelExpense->getDepartureDateTime()->format('Y-m-d H:i:s'));
        $arrivalDateTime = new \DateTime($travelExpense->getArrivalDateTime()->format('Y-m-d H:i:s'));
        $perDiem =  $travelExpenseService->calculatePerDiem(
            $arrivalDateTime,
            $departureDateTime
        );

        $travelExpenses = $travelExpenseService->sumExpenses($travelExpense);
        $sumCostsByCurrencies = $travelExpenseService->getCostsByCurrencies($travelExpense);
        $midRateDate = $travelExpenseService->getConversionDate($travelExpense);

        $advanceAmounts = $travelExpenseService->getAdvanceAmounts(
            $sumCostsByCurrencies['employeePaidExpenses'],
            $travelExpense
        );
        $totalAmountPayableInHUF = 0;
        foreach ($advanceAmounts as $amount) {
            $totalAmountPayableInHUF += $amount['amountInHUF'];
        }

        return $this->render(
            'OpitOpitHrmTravelBundle:Expense:viewTravelExpense.html.twig',
            array(
                'action' => $action,
                'travelExpense' => $travelExpense,
                'print' => true,
                'generalManager' => $generalManager,
                'advancesPayback' => $advanceAmounts,
                'totalAmountPayableInHUF' => $totalAmountPayableInHUF,
                'employee' => $employee,
                'datetime' => $approvedDateTime,
                'trId' => $travelRequest->getTravelRequestId(),
                'perDiem' => $perDiem,
                'expensesPaidByCompany' => $travelExpenses['companyPaidExpenses'],
                'expensesPaidByEmployee' => $travelExpenses['employeePaidExpenses'],
                'currencyFormat' => $currencyConfig['currency_format'],
                'midRate' => $midRateDate,
                'companyPaidExpenses' => $sumCostsByCurrencies['companyPaidExpenses'],
                'employeePaidExpenses' => $sumCostsByCurrencies['employeePaidExpenses'],
                'rates' => $this->container->get('opit.service.exchange_rates.default')
                               ->getRatesByDate($midRateDate)
            )
        );
    }

    /**
     * Return travel expense entity
     *
     * @param integer $travelExpenseId
     * @return mixed \Opit\OpitHrm\TravelBundle\Entity\TravelExpense or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getTravelExpense($travelExpenseId = null)
    {
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();

        if (null === $travelExpenseId) {
            $travelExpenseId = $request->request->get('id');
        }

        $travelExpense = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelExpense')->find($travelExpenseId);

        if (!$travelExpense) {
            throw $this->createNotFoundException('Missing travel expense for id "' . $travelExpenseId . '"');
        }

        return $travelExpense;
    }

    /**
     * Check if the access is granted.
     *
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @throws CreateNotFoundException
     */
    protected function isAccessGranted($travelRequest)
    {
        $securityContext = $this->get('security.context');
        if ($travelRequest->getUser()->getId() !== $securityContext->getToken()->getUser()->getId() && !$securityContext->isGranted('ROLE_ADMIN')) {
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
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelRequest $travelRequest
     * @param \Opit\OpitHrm\TravelBundle\Entity\TravelExpense $travelExpense
     * @param EntityManager $entityManager
     * @param ArrayCollection $children
     * @param Request $request
     * @return form
     */
    protected function handleForm($isNewTravelExpense, $travelRequest, $travelExpense, EntityManager $entityManager, $children, $request)
    {
        $form = $this->createForm(
            new ExpenseType(
                $travelRequest->getUser()->getEmployee(),
                $isNewTravelExpense
            ),
            $travelExpense,
            array('em' => $entityManager)
        );

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $statusManager = $this->get('opit.manager.travel_expense_status_manager');
                $isNew = $travelExpense->getId();
                $travelExpense = $this->get('opit.model.travel_expense')->calculateAdvances($travelExpense);

                $this->get('opit.model.travel_expense')->removeChildNodes($travelExpense, $children);
                $travelExpense->setTravelRequest($travelRequest);
                $entityManager->persist($travelExpense);
                $entityManager->flush();

                // Create initial state for new travel expense.
                if (null === $isNew) {
                    $statusManager->addStatus($travelExpense, Status::CREATED);
                }

                return true;
            }
        }

        return $form;
    }

    /**
     * Change the travel expense's state
     *
     * @param integer $foreignId travel expense Id
     * @param integer $statusId status id
     * @param string|null $comment the comment
     * @return \Symfony\Component\HttpFoundation\JsonResponse object
     */
    protected function changeExpenseState($foreignId, $statusId, $comment = null)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $travelExpense = $entityManager->getRepository('OpitOpitHrmTravelBundle:TravelExpense')
            ->find($foreignId);

        $this->get('opit.model.travel_expense')->changeStatus($travelExpense, $statusId, $comment);

        return new JsonResponse();
    }
}
