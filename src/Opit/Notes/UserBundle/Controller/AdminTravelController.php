<?php

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\TravelBundle\Entity\TEExpenseType;
use Opit\Notes\TravelBundle\Entity\TEPerDiem;
use Opit\Notes\TravelBundle\Form\PerDiemType;
use Opit\Notes\TravelBundle\Helper\Utils;

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
        $entityManager = $this->getDoctrine()->getManager();
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
        $entityManager = $this->getDoctrine()->getManager();
        $expenseTypeId = $this->getRequest()->request->get('id');
        
        if (!is_array($expenseTypeId)) {
            $expenseTypeId = array($expenseTypeId);
        }
        
        foreach ($expenseTypeId as $id) {
            $expenseType = $entityManager->getRepository('OpitNotesTravelBundle:TEExpenseType')->find($id);
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
     * @Route("/secured/admin/show/perdiem/{id}", name="OpitNotesUserBundle_admin_show_perdiem", defaults={"id" = "new"}, requirements={ "id" = "\d|new"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function showPerDiemAction(Request $request)
    {
        $id = $request->attributes->get('id');

        if ($id == 'new') {
            $index = null;
            $perDiem = new TEPerDiem();
        } else {
            $index = $request->attributes->get('index');
            $perDiem = $this->getPerDiem($id);
        }
        
        $form = $this->createForm(
            new PerDiemType(),
            $perDiem
        );
        return $this->render(
            'OpitNotesTravelBundle:Admin:showPerDiemForm.html.twig',
            array('form' => $form->createView(), 'index' => $index)
        );
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
        $data = $request->request->all();
        $em = $this->getDoctrine()->getManager();
        $result['response'] = 'success';
        $status = null;

        //If it was a post
        if ($request->isMethod('POST')) {
            
            $perDiemList = $em->getRepository('OpitNotesTravelBundle:TEPerDiem')->findAll();
            $ids = Utils::arrayValueRecursive('id', $data);
            
            // Remove per diems
            foreach ($perDiemList as $pd) {
                if (!in_array($pd->getId(), $ids)) {
                    // delete
                    $perDiem = $this->getPerDiem($pd->getId(), false);
                    $em->remove($perDiem);
                    $em->flush();
                }
            }
            if (!empty($data)) {
                // Save per diems
                foreach ($data['perdiem'] as $d) {
                    // save
                    $perDiem = $this->getPerDiem($d['id'], false);
                    $result = $this->setPerDiem($perDiem, $d);
                    if (500 === $result['status']) {
                        $status = $result['status'];
                        break;
                    }
                }
            }
        }
        return new JsonResponse(array('code' => $status, $result));
    }
    
    /**
     * Set the per diem entity
     *
     * @param \Opit\Notes\TravelBundle\Entity\TEPerDiem $perDiem
     * @param array $data value of per diem
     * @return int|boolean
     */
    protected function setPerDiem($perDiem, $data)
    {
        $em = $this->getDoctrine()->getManager();
        $result = array();
        $result['status'] = 200;
        $config = $this->container->getParameter('opit_notes_user');
        $currencyCode = $config['default_currency'];
        
        //If it is a new per diem create, else modify it.
        if (false === $perDiem) {
            // Create a new per diem and save it.
            $perDiem = new TEPerDiem();
        }
        
        if (isset($data['currency'])) {
            $currencyCode = $data['currency'];
        }
        $currency = $em->getRepository('OpitNotesCurrencyRateBundle:Currency')->findOneByCode($currencyCode);
        $perDiem->setHours($data['hours']);
        $perDiem->setAmount($data['amount']);
        $perDiem->setCurrency($currency);
        
        $validator = $this->get('validator');
        $errors = $validator->validate($perDiem);
        // If the validation failed
        if (count($errors) > 0) {
            $result['status'] = 500;
            $result['response'] = 'error';
            // Get the error messages.
            foreach ($errors as $e) {
                    $result['errorMessage'][] = $e->getMessage();
            }
        } else {
            $em->persist($perDiem);
            $em->flush();
        }
        return $result;
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
        
        if (!$perDiem || null === $perDiem) {
            //If this method throws error or just return with false value.
            if (true === $throwError) {
                throw $this->createNotFoundException('Missing Per diem for id "' . $perDiem . '"');
            } else {
                return false;
            }
        }
        return $perDiem;
    }
}
