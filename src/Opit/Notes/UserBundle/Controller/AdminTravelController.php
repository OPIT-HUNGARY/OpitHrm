<?php

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\TravelBundle\Entity\TEExpenseType;
use Opit\Notes\TravelBundle\Entity\TEPerDiem;
use Opit\Notes\TravelBundle\Form\PerDiemType;

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
    
    /**
     * To generate list per diem
     *
     * @Route("/secured/admin/list/perdiem", name="OpitNotesUserBundle_admin_list_perdiem")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listPerDiemAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $perDiemList = $em->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAll();

        return $this->render(
            $showList ? 'OpitNotesTravelBundle:Admin:_listPerDiem.html.twig' : 'OpitNotesTravelBundle:Admin:listPerDiem.html.twig',
            array('perDiems' => $perDiemList)
        );
    }
    
    /**
     * To show per diem
     *
     * @Route("/secured/admin/show/perdiem", name="OpitNotesUserBundle_admin_show_perdiem")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function showPerDiemAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        
        if ($id) {
            $perDiem = $this->getPerDiem($id);
        } else {
            $perDiem = new TEPerDiem();
        }
        
        $form = $this->createForm(
            new PerDiemType(),
            $perDiem
        );
        return $this->render('OpitNotesTravelBundle:Admin:showPerDiemForm.html.twig', array('form' => $form->createView()));
    }
    
    /**
     * To save per diem
     *
     * @Route("/secured/admin/save/perdiem", name="OpitNotesUserBundle_admin_save_perdiem")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function savePerDiemAction()
    {
        $request = $this->getRequest();
        $params = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $result['response'] = 'success';
        $form = $this->setPerDiemtForm();
        $status = null;
        
        // If it was  a post
        if ($request->isMethod('POST')) {
            
            foreach ($params as $key => $value) {

                foreach ($value as $v) {

                    if ('id'==$key) {

                        if (isset($value[$v])) {

                            $perDiem  = $this->getPerDiem($value[$v], false);
                            $hours = $params['hours'][$v];
                            $amount = $params['ammount'][$v];
                            $isToDelete = $params['isToDelete'][$v];

                            $saveResult = $this->setPerDiem($em, $perDiem, $hours, $amount, $isToDelete);

                            if (isset($saveResult['deletedElement']) &&  true===$saveResult['deletedElement']) {
                                continue;
                            }
                            $status = $saveResult['status'];
                        }
                    }
                }
            }
        }
        return new JsonResponse(array('code' => $status, $result));
    }
    
    /**
     * Returns a Per Diem request object
     *
     * @param integer $perDiemId
     * @return mixed  TEPerDiem object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getPerDiem($perDiemId = null, $throwError = true)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $perDiemId) {
            $perDiemId = $request->request->get('id');
        }

        $perDiem = $em->getRepository('OpitNotesTravelBundle:TEPerDiem')->find($perDiemId);
        
        if (!$perDiem) {
            
            if (true === $throwError) {
                throw $this->createNotFoundException('Missing Per diem for id "' . $perDiem . '"');
            } else {
                return false;
            }
        }
        return $perDiem;
    }
    
    protected function setPerDiem($em, $perDiem, $hours, $amount, $isToDelete)
    {
        $result = array();
        $result['status'] = 200;
        
        if (($amount > 0 && $hours > 0) && ($amount !== '' && $hours !== '')) {
            if (false === $perDiem) {

                    $perDiem = new TEPerDiem();
                    $perDiem->setHours($hours);
                    $perDiem->setAmmount($amount);
                    $em->persist($perDiem);
                    $em->flush();
            } else {
                
                if ('1' == $isToDelete) {
                    $em->remove($perDiem);
                    $em->flush();
                    $result['deletedElement'] = true;
                    return $result;
                }
                $perDiem->setHours($hours);
                $perDiem->setAmmount($amount);
                $em->persist($perDiem);
                $em->flush();
            }
        } else {
            $result['status'] = 500;
        }
        return $result;
    }


    /**
     *  Set the Per diem form
     * @return Per diem object $form
     */
    protected function setPerDiemtForm()
    {
        $form = $this->createForm(
            new PerDiemType()
        );
        
        return $form;
    }
}
