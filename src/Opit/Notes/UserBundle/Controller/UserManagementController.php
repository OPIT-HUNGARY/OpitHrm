<?php

namespace Opit\Notes\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class UserManagementController extends Controller 
{
    
    /**
     * @Route("/usermanagement/list")
     * @Template()
     */
    public function listAction()
    {        
        $userList = $this->queryUsers(true);
        $userAttributes = array();
        
            $user = $userList[0];
            $reflect = new \ReflectionObject($user);
            $props   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
            
            foreach ($props as $prop) {
                $userAttributes[] = $prop->getName();
            }
        
        var_dump($userAttributes);
        
        return array("userList"=>$userList);
    }
    
    protected function queryUsers($getAllUsers=false)
    {
        if($getAllUsers)
        {
            return $this->getDoctrine()->getRepository('OpitNotesUserBundle:User')->findAll();
        } else { 
            
        }
    }
}
