<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\Notes\UserBundle\Form\UserShowType;
use Opit\Notes\UserBundle\Form\ChangePasswordType;
use Opit\Notes\UserBundle\Entity\User;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\UserBundle\Entity\Groups;

/**
 * Description of AdminController
 *
 * @author OPIT\Notes
 */
class AdminController extends Controller
{
    /**
     * @Route("/secured/admin/groups/list", name="OpitNotesUserBundle_admin_groups_list")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function groupsListAction()
    {
        $group = $this->getDoctrine()->getRepository('OpitNotesUserBundle:Groups')->findAll();
        $disabledRoles = array();
        $numberOfRelations = array();

        foreach ($group as $g) {
            $users = $g->getUsers();
            if (0 !== count($users)) {
                $disabledRoles[] = $g->getId();
            }
            $numberOfRelations[$g->getId()] = count($g->getUsers());
        }
        
        return array(
                'propertyNames' => array('id', 'name', 'role'),
                'propertyValues' => $group,
                'hideReset' => true,
                'disabledRoles' => $disabledRoles,
                'numberOfRelations' => $numberOfRelations
        );
    }
   
    /**
     * @Route("/secured/admin/groups/show/{id}", name="OpitNotesUserBundle_admin_groups_show", requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */    
    public function groupsShowAction()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $groupId = $request->attributes->get('id');
        
        $requestGroup = $request->request->get('group');
        $groupRoleName = 'ROLE_' . strtoupper($requestGroup);
        $groupName = ucfirst($requestGroup);
        
        if ('new' === $groupId) {
            $group = new Groups();
        } else {
            $group = $entityManager->getRepository('OpitNotesUserBundle:Groups')->find($groupId);            
        }
        
        $group->setName($groupName);
        $group->setRole($groupRoleName);    
        $entityManager->persist($group);
        
        $role = $this->getDoctrine()
             ->getRepository('OpitNotesUserBundle:Groups')
             ->findOneBy(array('name' => $groupName, 'role'=>$groupRoleName));

        if (null !== $role) {
            return new JsonResponse(array('duplicate' => true));
        } else {
            $entityManager->flush();   
        }
        
        $group = $this->getDoctrine()->getRepository('OpitNotesUserBundle:Groups')->findAll();
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig', 
            $this->groupsListAction()
        );
    }
    
    /**
     * @Route("/secured/admin/groups/delete", name="OpitNotesUserBundle_admin_groups_delete")
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */      
    public function deleteGroupAction()
    {
        $entityManager = $this->getDoctrine()->getEntityManager();
        $groupId = $this->getRequest()->request->get('id');
        $userRelated = array();
        
        if (is_array($groupId)) {
            foreach ($groupId as $id) {
                $group = $entityManager->getRepository('OpitNotesUserBundle:Groups')->find($id);
                if (0 === count($group->getUsers())) {
                    $entityManager->remove($group);
                } else {
                    $userRelated[] = $group->getName();
                }
            }
        } else {
            $group = $entityManager->getRepository('OpitNotesUserBundle:Groups')->find($groupId);
            if (0 === count($group->getUsers())) {
                $entityManager->remove($group);
            } else {
                $userRelated[] = $group->getName();
            }
        }
        $entityManager->flush();
        
        $group = $this->getDoctrine()->getRepository('OpitNotesUserBundle:Groups')->findAll();
        
        if(count($userRelated) > 0) {
            return new JsonResponse(array('userRelated' => $userRelated));
        }
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig', 
            $this->groupsListAction()
        );
    }
}
