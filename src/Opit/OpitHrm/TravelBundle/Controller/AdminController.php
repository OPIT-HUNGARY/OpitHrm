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
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\OpitHrm\TravelBundle\Form\TransportationType as TransportationTypeForm;
use Opit\OpitHrm\TravelBundle\Entity\TransportationType;
use Opit\OpitHrm\TravelBundle\Entity\TEExpenseType;
use Opit\OpitHrm\TravelBundle\Entity\TEPerDiem;
use Opit\OpitHrm\TravelBundle\Form\PerDiemType;
use Opit\Component\Utils\Utils;

/**
 * AdminController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage TravelBundle
 *
 * @Route("/secured")
 */
class AdminController extends Controller
{
    /**
     * List expense types
     *
     * @Route("/travel/admin/travelexpensetype/list", name="OpitOpitHrmTravelBundle_admin_travelexpensetype_list")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function listExpenseTypeAction(Request $request)
    {
        $orderParams = $this->getRequest()->get('order');

        if ($this->getRequest()->get('issearch')) {
            // Find by order parameters.
            $expenseTypes = $this->getDoctrine()->getRepository('OpitOpitHrmTravelBundle:TEExpenseType')->findBy(
                array(),
                array($orderParams['field'] => $orderParams['dir'])
            );
        } else {
            $expenseTypes = $this->getDoctrine()->getRepository('OpitOpitHrmTravelBundle:TEExpenseType')->findAll();
        }

        if ($request->request->get('showList')) {
            $template = 'OpitOpitHrmTravelBundle:Admin:_expensetypeList.html.twig';
        } else {
            $template = 'OpitOpitHrmTravelBundle:Admin:expensetypeList.html.twig';
        }

        return $this->render(
            $template,
            array(
                'expenseTypes' => $expenseTypes
            )
        );
    }

    /**
     * Show expense type
     *
     * @Route("/travel/admin/travelexpensetype/show/{id}", name="OpitOpitHrmTravelBundle_admin_travelexpensetype_show", requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"GET", "POST"})
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
                $entityManager->getRepository('OpitOpitHrmTravelBundle:TEExpenseType')->find($expenseTypeId);
        }

        $travelExpense->setName($request->request->get('value'));
        $entityManager->persist($travelExpense);
        $entityManager->flush();

        $expenseTypes = $this->getDoctrine()->getRepository('OpitOpitHrmTravelBundle:TEExpenseType')->findAll();

        return $this->render(
            'OpitOpitHrmTravelBundle:Admin:_expensetypeList.html.twig',
            array(
                'expenseTypes' => $expenseTypes
            )
        );
    }

    /**
     * @Route("/travel/admin/expensetype/delete", name="OpitOpitHrmTravelBundle_admin_expensetype_delete")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
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
            $expenseType = $entityManager->getRepository('OpitOpitHrmTravelBundle:TEExpenseType')->find($id);
            $entityManager->remove($expenseType);
        }

        $entityManager->flush();

        $expenseTypes = $this->getDoctrine()->getRepository('OpitOpitHrmTravelBundle:TEExpenseType')->findAll();

        return $this->render(
            'OpitOpitHrmTravelBundle:Admin:_expensetypeList.html.twig',
            array(
                'expenseTypes' => $expenseTypes
            )
        );
    }

    /**
     * To generate list per diem
     *
     * @Route("/travel/admin/list/perdiem", name="OpitOpitHrmTravelBundle_admin_list_perdiem")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Template()
     */
    public function listPerDiemAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $perDiemList = $em->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')->findAll();

        return $this->render(
            $showList ? 'OpitOpitHrmTravelBundle:Admin:_listPerDiem.html.twig' : 'OpitOpitHrmTravelBundle:Admin:listPerDiem.html.twig',
            array('perDiems' => $perDiemList)
        );
    }

    /**
     * To show per diem
     *
     * @Route("/travel/admin/show/perdiem/{id}", name="OpitOpitHrmTravelBundle_admin_show_perdiem", defaults={"id" = "new"}, requirements={ "id" = "\d|new"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
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
            'OpitOpitHrmTravelBundle:Admin:showPerDiemForm.html.twig',
            array('form' => $form->createView(), 'index' => $index)
        );
    }

    /**
     * To save per diem
     *
     * @Route("/travel/admin/save/perdiem", name="OpitOpitHrmTravelBundle_admin_save_perdiem")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
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

            $perDiemList = $em->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')->findAll();
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
     * @param \Opit\OpitHrm\TravelBundle\Entity\TEPerDiem $perDiem
     * @param array $data value of per diem
     * @return int|boolean
     */
    protected function setPerDiem($perDiem, $data)
    {
        $em = $this->getDoctrine()->getManager();
        $result = array();
        $result['status'] = 200;
        $config = $this->container->getParameter('currency_config');
        $currencyCode = $config['default_currency'];

        //If it is a new per diem create, else modify it.
        if (false === $perDiem) {
            // Create a new per diem and save it.
            $perDiem = new TEPerDiem();
        }

        if (isset($data['currency'])) {
            $currencyCode = $data['currency'];
        }
        $currency = $em->getRepository('OpitOpitHrmCurrencyRateBundle:Currency')->findOneByCode($currencyCode);
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

        $perDiem = $em->getRepository('OpitOpitHrmTravelBundle:TEPerDiem')->find($perDiemId);

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

    /**
     * Renders the transportation type list template
     *
     * @Route("/travel/admin/transportation_type/list", name="OpitOpitHrmTravelBundle_admin_transportationtype_list")
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Template()
     */
    public function listTransportationTypesAction(Request $request)
    {
        $orderParams = $request->request->get('order');
        $entityManager = $this->getDoctrine()->getManager();

        if ($this->getRequest()->get('issearch')) {
            // Find by order parameters.
            $transportationTypes = $entityManager->getRepository('OpitOpitHrmTravelBundle:TransportationType')
                ->findBy(array(), array($orderParams['field'] => $orderParams['dir']));
        } else {
            $transportationTypes = $entityManager->getRepository('OpitOpitHrmTravelBundle:TransportationType')
                ->findAll();
        }

        // Return for ajax post requests
        if ($request->isXmlHttpRequest()) {
            return $this->render(
                'OpitOpitHrmTravelBundle:Admin:_listTransportationTypes.html.twig',
                array('transportationTypes' => $transportationTypes)
            );
        }

        return array('transportationTypes' => $transportationTypes);
    }

    /**
     * Renders a transportation type form
     *
     * @Route("/travel/admin/transportation_type/show/{id}", name="OpitOpitHrmTravelBundle_admin_transportationtype_show", requirements={ "id" = "new|\d+"}, defaults={"id" = "new"})
     * @Method({"GET", "POST"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Template()
     */
    public function showTransportationTypeAction(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $result = array('response' => 'success');
        $statusCode = 200;

        if ('new' === $id) {
            $transportationType = new TransportationType();
        } else {
            $transportationType = $entityManager->getRepository('OpitOpitHrmTravelBundle:TransportationType')
                ->find($id);
        }

        $form = $this->createForm(new TransportationTypeForm(), $transportationType);

        // Handle post data and persist
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $entityManager->persist($transportationType);
                $entityManager->flush();
            } else {
                $statusCode = 500;
                $result['response'] = 'error';
                $result['errorMessage'] = Utils::getErrorMessages($form);
            }

            return new JsonResponse(array($result), $statusCode);
        }

        return array('form' => $form->createView());
    }

    /**
     * Deletes one or more transportation types
     *
     * @Route("/travel/admin/transportation_type/delete", name="OpitOpitHrmTravelBundle_admin_transportationtype_delete")
     * @Method({"POST"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     */
    public function deleteTransportationTypesAction(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $ids = $request->request->get('delete-type');

        $transportationTypes = $entityManager->getRepository('OpitOpitHrmTravelBundle:TransportationType')
            ->findById($ids);

        if (count($transportationTypes) === 0) {
            return $this->createNotFoundException('No transportation types found.');
        }

        foreach ($transportationTypes as $transportationType) {
            $entityManager->remove($transportationType);
        }

        $entityManager->flush();

        return new JsonResponse(array('response' => 'success'));
    }
}
