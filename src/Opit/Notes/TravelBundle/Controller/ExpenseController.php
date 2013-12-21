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
        $isNewTravelExpense = "new" !== $travelExpenseId;
        $securityContext = $this->get('security.context');
        $currentUser = $securityContext->getToken()->getUser();
        
        $travelExpense = ($isNewTravelExpense) ? $this->getTravelExpense($travelExpenseId) : new TravelExpense();
        
        if (false === $isNewTravelExpense) {
            $travelExpense->setUser($currentUser);
        }
        
        $children = new ArrayCollection();
        
        foreach ($travelExpense->getCompanyPaidExpenses() as $companyPaidExpenses) {
            $children->add($companyPaidExpenses);
        }
        
        foreach ($travelExpense->getUserPaidExpenses() as $userPaidExpenses) {
            $children->add($userPaidExpenses);
        }
        
        $entityManager->getFilters()->disable('softdeleteable');
        
        $form = $this->createForm(
            new ExpenseType($this->get('security.context')->isGranted('ROLE_ADMIN'), $isNewTravelExpense),
            $travelExpense,
            array('em' => $entityManager)
        );
        
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            
            if ($form->isValid()) {
                $this->removeChildNodes($entityManager, $travelExpense, $children);
                
                $entityManager->persist($travelExpense);
                $entityManager->flush();
                
                return $this->redirect($this->generateUrl('OpitNotesTravelBundle_expense_list'));
            }
        }
        
        return array('form' => $form->createView(), 'travelExpense' => $travelExpense);
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
            $getter = ($child instanceof TEUserPaidExpense) ? 'getUserPaidExpenses' : 'getCompanyPaidExpenses';
            if (false === $travelExpense->$getter()->contains($child)) {
                $child->setTravelExpense(null);
                $entityManager->remove($child);
            }
        }
    }
}
