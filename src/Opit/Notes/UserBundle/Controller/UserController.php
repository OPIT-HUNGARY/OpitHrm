<?php

namespace Opit\Notes\UserBundle\Controller;

use Opit\Notes\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class UserController extends Controller
{
    
    /**
     * @Route("/secured/user/list", name="OpitNotesUserBundle_user_list")
     * @Template()
     */
    public function listAction()
    {
        //get all rows from database table
        $users = $this->getDoctrine()->getRepository('OpitNotesUserBundle:User')->findAll();
        $entityManager = $this->getDoctrine()->getManager();
        $groups = $entityManager->getRepository('OpitNotesUserBundle:Groups');
        $propertyValues = array();
        
        foreach ($users as $user) {
            //fetch roles for the user
            $localUserRoles = $groups->findUserGroupsArray($user->getId());
            $roles = array();
            
            //get all user roles and put them in an array
            foreach ($localUserRoles as $role) {
                $roles[] = $role["name"];
            }
            
            //create new array for user containing its properties
            $propertyValues[$user->getId()] = array(
                    "username" => $user->getUsername(),
                    "email" => $user->getEmail(),
                    "employeeName" => $user->getEmployeeName(),
                    "isActive" => $user->getIsActive(),
                    "roles" => $roles
                );
            }
            
            $propertyNames = array("username", "email", "employeeName", "isActive", "roles");

        return array("propertyNames" => $propertyNames, "propertyValues" => $propertyValues);
    }
    
/**
    * @Route("/secured/user/search", name="OpitNotesUserBundle_user_search")
    * @Template()
    * @Method({"POST"})
    */
    public function searchAction()
    {
        $request = $this->getRequest()->request->all();
        $empty = array_filter($request, function($value) { return !empty($value); });
        
        if(array_key_exists('resetForm', $request) || empty($empty)) {
            list($propertyNames, $propertyValues) = array_values($this->listAction());
        } else {
            $propertyNames = array("username", "email", "employeeName", "isActive", "roles");
            $propertyValues = array();

            $entityManager = $this->getDoctrine()->getManager();
            $result = $entityManager->getRepository('OpitNotesUserBundle:User')
                    ->findUsersByPropertyUsingLike($request);

            $groups = $entityManager->getRepository('OpitNotesUserBundle:Groups');

            $users = array();
            for ($i = 0; $i < count($result); $i++) {
                $user = $result[$i];
                $id = $user->getId();
                $roles = array();
                $localUserRoles = $groups->findUserGroupsArray($id);
                foreach ($localUserRoles as $role) {
                    $roles[] = $role["name"];
                }

                $propertyValues[$id] = array(
                        "username" => $user->getUsername(),
                        "email" => $user->getEmail(),
                        "employeeName" => $user->getEmployeeName(),
                        "isActive" => $user->getIsActive(),
                        "roles" => $roles
                    );
            }
        }
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig',
                array("propertyNames" => $propertyNames, "propertyValues" => $propertyValues));
    }
}