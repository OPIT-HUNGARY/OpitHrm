<?php

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\TravelBundle\Entity\TEExpenseType;

/**
 * Description of AdminController
 *
 * @author OPIT\Notes
 */
class AdminTravelController extends Controller
{
    /**
     * List expense types
     *
     * @Route("/secured/admin/travelexpensetype/list", name="OpitNotesUserBundle_admin_travelexpensetype_list")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listExpenseTypeAction()
    {
        $expenseTypes = $this->getDoctrine()->getRepository('OpitNotesTravelBundle:TEExpenseType')->findAll();
        
        return $this->render(
            'OpitNotesUserBundle:Admin:Travel/expensetypeList.html.twig',
            array(
                'propertyNames' => array('id', 'name'),
                'propertyValues' => $expenseTypes
            )
        );
    }
    
    /**
     * Show expense type
     *
     * @Route("/secured/admin/travelexpensetype/show/{id}", name="OpitNotesUserBundle_admin_travelexpensetype_show", requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */
    public function expenseTypeShowAction()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $expenseTypeId = $request->attributes->get('id');
        
        if ('new' === $expenseTypeId) {
            $travelExpense = new TEExpenseType();
        } else {
            $travelExpense =
                $entityManager->getRepository('OpitNotesTravelBundle:TEExpenseType')->find($expenseTypeId);
        }
        
        $travelExpense->setName($request->request->get('value'));
        $entityManager->persist($travelExpense);
        $entityManager->flush();
        
        $expenseTypes = $this->getDoctrine()->getRepository('OpitNotesTravelBundle:TEExpenseType')->findAll();
        
        return $this->render(
            'OpitNotesUserBundle:Shared:_list.html.twig',
            array(
                'propertyNames' => array('id', 'name'),
                'propertyValues' => $expenseTypes,
                'hideReset' => ''
            )
        );
    }
    
    /**
     * @Route("/secured/admin/expensetype/delete", name="OpitNotesUserBundle_admin_expensetype_delete")
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */
    public function deleteExpenseTypeAction()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        $expenseTypeId = $this->getRequest()->request->get('id');
        
        if (is_array($expenseTypeId)) {
            foreach ($expenseTypeId as $id) {
                $expenseType = $entityManager->getRepository('OpitNotesTravelBundle:TEExpenseType')->find($id);
                $entityManager->remove($expenseType);
            }
        } else {
            $expenseType = $entityManager->getRepository('OpitNotesTravelBundle:TEExpenseType')->find($expenseTypeId);
            $entityManager->remove($expenseType);
        }
        $entityManager->flush();
        
        $expenseTypes = $this->getDoctrine()->getRepository('OpitNotesTravelBundle:TEExpenseType')->findAll();
        
        return $this->render(
            'OpitNotesUserBundle:Shared:_list.html.twig',
            array(
                'propertyNames' => array('id', 'name'),
                'propertyValues' => $expenseTypes,
                'hideReset' => ''
            )
        );
    }
}
