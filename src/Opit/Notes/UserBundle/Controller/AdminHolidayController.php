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

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\HolidayBundle\Entity\HolidayCategory;
use Opit\Notes\HolidayBundle\Form\HolidayCategoryType;

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
     * @Route("/secured/admin/list/holiday/categories", name="OpitNotesUserBundle_admin_list_holiday_categories")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listHolidayCategoriesAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $holidayCategories = $em->getRepository('OpitNotesHolidayBundle:HolidayCategory')->findAll();
        $disabledHolidayCategories = array();

        return $this->render(
            'OpitNotesUserBundle:Admin:Holiday/' . ($showList ? '_' : '') . 'listHolidayCategories.html.twig',
            array('holidayCategories' => $holidayCategories, 'disabledHolidayCategories' => $disabledHolidayCategories)
        );
    }
    
    /**
     * To generate add/edit job title form
     *
     * @Route("/secured/admin/add/holiday/category/{id}", name="OpitNotesUserBundle_admin_add_holiday_category", requirements={ "id" = "\d+"})
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
     * To generate show holiday category form
     *
     * @Route("/secured/admin/show/holiday/category/{id}", name="OpitNotesUserBundle_admin_show_holiday_category", requirements={"id" = "\d+"})
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
            'OpitNotesUserBundle:Admin:Holiday/showHolidayCategoryForm.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * To delete holiday categories in Notes
     *
     * @Route("/secured/admin/delete/holiday/category", name="OpitNotesUserBundle_admin_delete_holiday_category")
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
}
