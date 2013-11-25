<?php

namespace Opit\Notes\UserBundle\Controller;

use Opit\Notes\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

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
}