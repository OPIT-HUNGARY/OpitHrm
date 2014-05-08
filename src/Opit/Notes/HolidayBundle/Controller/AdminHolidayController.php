<?php

/*
 * The MIT License
 *
 * Copyright 2014 OPIT\bota.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Opit\Notes\HolidayBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\HolidayBundle\Entity\HolidayCategory;
use Opit\Notes\HolidayBundle\Form\HolidayCategoryType;
use Opit\Notes\HolidayBundle\Entity\HolidayDate;
use Opit\Notes\HolidayBundle\Form\HolidayDateType;
use Opit\Notes\HolidayBundle\Entity\HolidayType;
use Opit\Notes\HolidayBundle\Form\HolidayTypeType;

/**
 * Description of AdminHolidayController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class AdminHolidayController extends Controller
{
    /**
     * To generate list holiday categories
     *
     * @Route("/secured/admin/list/holiday/categories", name="OpitNotesHolidayBundle_admin_list_holiday_categories")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listHolidayCategoriesAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $holidayCategories = $em->getRepository('OpitNotesHolidayBundle:HolidayCategory')->findAll();

        return $this->render(
            'OpitNotesHolidayBundle:Admin:' . ($showList ? '_' : '') . 'listHolidayCategories.html.twig',
            array('holidayCategories' => $holidayCategories)
        );
    }
    
    /**
     * To generate add/edit leave category form
     *
     * @Route("/secured/admin/add/holiday/category/{id}", name="OpitNotesHolidayBundle_admin_add_holiday_category", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addHolidayCategoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $holidayCategory = $this->getHolidayCategory($request->attributes->get('id'));
        } else {
            $holidayCategory = new HolidayCategory();
        }

        $form = $this->createForm(new HolidayCategoryType(), $holidayCategory);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($holidayCategory);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($holidayCategory);

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
            }
            $result['errorMessage'] = $errorMessages;
        }
        return new JsonResponse(array($result));
    }
    
    /**
     * To generate show leave category form
     *
     * @Route("/secured/admin/show/holiday/category/{id}", name="OpitNotesHolidayBundle_admin_show_holiday_category", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showHolidayCategoryFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $holidayCategory = $this->getHolidayCategory($id);
        } else {
            $holidayCategory = new HolidayCategory();
        }

        $form = $this->createForm(
            new HolidayCategoryType(),
            $holidayCategory
        );
        
        return $this->render(
            'OpitNotesHolidayBundle:Admin:showHolidayCategoryForm.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * To delete leave categories in Notes
     *
     * @Route("/secured/admin/delete/holiday/category", name="OpitNotesHolidayBundle_admin_delete_holiday_category")
     * @Method({"POST"})
     */
    public function deleteHolidayCategoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-holidaycategory');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $holidayCategory = $this->getHolidayCategory($id);
            $em->remove($holidayCategory);
        }
        $em->flush();
        $result['response'] = 'success';

        return new JsonResponse(array('code' => 200, $result));
    }
    
    /**
     * Returns a holidayCategory object
     *
     * @param integer $holidayCategoryId
     * @return mixed  holidayCategory object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getHolidayCategory($holidayCategoryId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $holidayCategoryId) {
            $holidayCategoryId = $request->request->get('id');
        }

        $holidayCategory = $em->getRepository('OpitNotesHolidayBundle:HolidayCategory')->find($holidayCategoryId);

        if (!$holidayCategory) {
            throw $this->createNotFoundException('Missing job title for id "' . $holidayCategoryId . '"');
        }

        return $holidayCategory;
    }
    
    /**
     * To generate list Administrative Leave/Working Day
     *
     * @Route("/secured/admin/list/holiday/dates", name="OpitNotesHolidayBundle_admin_list_holiday_dates")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listHolidayDateAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $holidayDates = $em->getRepository('OpitNotesHolidayBundle:HolidayDate')->findBy(array(), array('holidayDate' => 'DESC'));
        $groupedHolidayDates = array();

        // Grouping the holiday dates by year
        foreach ($holidayDates as $date) {
            $groupedHolidayDates[substr($date->getHolidayDate()->format('Y-m-d'), 0, 4)][] = $date;
        }

        return $this->render(
            'OpitNotesHolidayBundle:Admin:' . ($showList ? '_' : '') . 'listHolidayDates.html.twig',
            array('groupedHolidayDates' => $groupedHolidayDates)
        );
    }

    /**
     * To generate show Administrative Leave/Working Day form
     *
     * @Route("/secured/admin/show/holiday/date/{id}", name="OpitNotesHolidayBundle_admin_show_holiday_date", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showHolidayDateFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $holidayDate = $this->getHolidayDate($id);
        } else {
            $holidayDate = new HolidayDate();
        }

        $form = $this->createForm(
            new HolidayDateType(),
            $holidayDate
        );

        return $this->render(
            'OpitNotesHolidayBundle:Admin:showHolidayDateForm.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * Returns a holidayDate object
     *
     * @param integer $holidayDateId
     * @return mixed  holidayDate object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getHolidayDate($holidayDateId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $holidayDateId) {
            $holidayDateId = $request->request->get('id');
        }

        $holidayDate = $em->getRepository('OpitNotesHolidayBundle:HolidayDate')->find($holidayDateId);

        if (!$holidayDate) {
            throw $this->createNotFoundException('Missing job title for id "' . $holidayDateId . '"');
        }

        return $holidayDate;
    }
    
    /**
     * To generate add/edit Administrative Leave/Working Day form
     *
     * @Route("/secured/admin/add/holiday/date/{id}", name="OpitNotesHolidayBundle_admin_add_holiday_date", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addHolidayDateAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $holidayDate = $this->getHolidayDate($request->attributes->get('id'));
        } else {
            $holidayDate = new HolidayDate();
        }

        $form = $this->createForm(new HolidayDateType(), $holidayDate);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($holidayDate);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($holidayDate);

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
            }
            $result['errorMessage'] = $errorMessages;
        }

        return new JsonResponse(array($result));
    }
    
    /**
     * To delete Administrative Leave/Working Day in Notes
     *
     * @Route("/secured/admin/delete/holiday/date", name="OpitNotesHolidayBundle_admin_delete_holiday_date")
     * @Method({"POST"})
     */
    public function deleteHolidayDateAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-holidaydate');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $holidayDate = $this->getHolidayDate($id);
            $em->remove($holidayDate);
        }
        $em->flush();
        $result['response'] = 'success';

        return new JsonResponse(array('code' => 200, $result));
    }
    
    /**
     * To generate list Administrative Leave/Working Day types
     *
     * @Route("/secured/admin/list/holiday/types", name="OpitNotesHolidayBundle_admin_list_holiday_types")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listHolidayTypeAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $holidayTypes = $em->getRepository('OpitNotesHolidayBundle:HolidayType')->findAll();

        return $this->render(
            'OpitNotesHolidayBundle:Admin:' . ($showList ? '_' : '') . 'listHolidayTypes.html.twig',
            array('holidayTypes' => $holidayTypes)
        );
    }
    
    /**
     * To generate show Administrative Leave/Working Day type form
     *
     * @Route("/secured/admin/show/holiday/type/{id}", name="OpitNotesHolidayBundle_admin_show_holiday_type", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showHolidayTypeFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $holidayType = $this->getHolidayType($id);
        } else {
            $holidayType = new HolidayType();
        }

        $form = $this->createForm(
            new HolidayTypeType(),
            $holidayType
        );

        return $this->render(
            'OpitNotesHolidayBundle:Admin:showHolidayTypeForm.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * Returns a Administrative Leave/Working Day type object
     *
     * @param integer $holidayTypeId
     * @return mixed  holidayType object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getHolidayType($holidayTypeId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $holidayTypeId) {
            $holidayTypeId = $request->request->get('id');
        }

        $holidayType = $em->getRepository('OpitNotesHolidayBundle:HolidayType')->find($holidayTypeId);

        if (!$holidayType) {
            throw $this->createNotFoundException('Missing holiday type for id "' . $holidayTypeId . '"');
        }

        return $holidayType;
    }
    
    /**
     * To generate add/edit Administrative Leave/Working Day type form
     *
     * @Route("/secured/admin/add/holiday/type/{id}", name="OpitNotesHolidayBundle_admin_add_holiday_type", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addHolidayTypeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $holidayType = $this->getHolidayType($request->attributes->get('id'));
        } else {
            $holidayType = new HolidayType();
        }

        $form = $this->createForm(new HolidayTypeType(), $holidayType);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($holidayType);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($holidayType);

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
            }
            $result['errorMessage'] = $errorMessages;
        }

        return new JsonResponse(array($result));
    }
    
    /**
     * To delete Administrative Leave/Working Day types in Notes
     *
     * @Route("/secured/admin/delete/holiday/type", name="OpitNotesHolidayBundle_admin_delete_holiday_type")
     * @Method({"POST"})
     */
    public function deleteHolidayTypeAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-holidaytype');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            $holidayType = $this->getHolidayType($id);
            $em->remove($holidayType);
        }
        $em->flush();
        $result['response'] = 'success';

        return new JsonResponse(array('code' => 200, $result));
    }
}
