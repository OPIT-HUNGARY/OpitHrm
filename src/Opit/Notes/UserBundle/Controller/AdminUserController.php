<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\Notes\UserBundle\Entity\JobTitle;
use Opit\Notes\UserBundle\Form\JobTitleType;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Notes\UserBundle\Entity\Groups;
use Opit\Notes\UserBundle\Entity\Teams;

/**
 * Description of AdminController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class AdminUserController extends Controller
{
    /**
     * To generate list job title
     *
     * @Route("/secured/admin/list/jobtitle", name="OpitNotesUserBundle_admin_list_jobtitle")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listJobTitleAction()
    {
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');
        $em = $this->getDoctrine()->getManager();
        $jobTitles = $em->getRepository('OpitNotesUserBundle:JobTitle')->findAll();
        $disabledJobTitles = $this->getAssignedJobTitlesToUsers();

        return $this->render(
            'OpitNotesUserBundle:Admin:' . ($showList ? '_' : '') . 'listJobTitle.html.twig',
            array('jobTitles' => $jobTitles, 'disabledJobTitles' => $disabledJobTitles)
        );
    }

    /**
     * To generate show job title form
     *
     * @Route("/secured/admin/show/jobtitle/{id}", name="OpitNotesUserBundle_admin_show_jobtitle", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showJobTitleFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $jobTitle = $this->getJobTitle($id);
        } else {
            $jobTitle = new JobTitle();
        }

        $form = $this->createForm(
            new JobTitleType(),
            $jobTitle
        );
        return $this->render(
            'OpitNotesUserBundle:Admin:showJobTitleForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * To generate add/edit job title form
     *
     * @Route("/secured/admin/add/jobtitle/{id}", name="OpitNotesUserBundle_admin_add_jobtitle", requirements={ "id" = "\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function addJobTitleAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');

        if ($id) {
            $jobTitle = $this->getJobTitle($request->attributes->get('id'));
        } else {
            $jobTitle = new JobTitle();
        }

        $form = $this->createForm(new JobTitleType(), $jobTitle);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($jobTitle);
                $em->flush();

                $result['response'] = 'success';
            }

            $validator = $this->get('validator');
            $errors = $validator->validate($jobTitle);

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
     * To delete job titles in Notes
     *
     * @Route("/secured/admin/delete/jobtitle", name="OpitNotesUserBundle_admin_delete_jobtitle")
     * @Method({"POST"})
     */
    public function deleteJobTitleAction()
    {
        $em = $this->getDoctrine()->getManager();
        $disabledJobTitles = $this->getAssignedJobTitlesToUsers();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-jobtitle');
        $result = array('response' => 'error');
        $deleteDisabled = false;

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {

            if (!array_key_exists($id, $disabledJobTitles)) {
                $jobTitle = $this->getJobTitle($id);
                $em->remove($jobTitle);
            } else {
                $deleteDisabled = true;
            }

        }
        $em->flush();
        $result['response'] = 'success';

        if (true === $deleteDisabled) {
            $result['userRelated'] = true;
        }
        return new JsonResponse(array('code' => 200, $result));
    }

    /**
     * Returns a jobTitle request object
     *
     * @param integer $jobTitleId
     * @return mixed  jobTitle object or null
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getJobTitle($jobTitleId = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $jobTitleId) {
            $jobTitleId = $request->request->get('id');
        }

        $jobTitle = $em->getRepository('OpitNotesUserBundle:JobTitle')->find($jobTitleId);

        if (!$jobTitle) {
            throw $this->createNotFoundException('Missing job title for id "' . $jobTitleId . '"');
        }

        return $jobTitle;
    }

    /**
     * Get an array about which job titles are assigned to users.
     *
     * @return array $disabledJobTitles key is job title id, value is the number of relations.
     */
    private function getAssignedJobTitlesToUsers()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('OpitNotesUserBundle:User')->findAll();
        $disabledJobTitles = array();

        foreach ($users as $u) {
            $userJobTitle = $u->getJobTitle();
            if (null !== $userJobTitle) {
                if (isset($disabledJobTitles[$userJobTitle->getId()])) {
                    $disabledJobTitles[$userJobTitle->getId()] += 1;
                } else {
                    $disabledJobTitles[$userJobTitle->getId()] = 1;
                }
            }
        }
        return $disabledJobTitles;
    }
    
    /**
     * @Route("/secured/admin/groups/list", name="OpitNotesUserBundle_admin_groups_list")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function groupsListAction()
    {
        return $this->render(
            'OpitNotesUserBundle:Admin:groupsList.html.twig',
            $this->getAllGroups()
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
        $entityManager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $groupId = $request->attributes->get('id');
        
        $requestGroup = $request->request->get('value');
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
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig', $this->getAllGroups());
    }
    
    /**
     * @Route("/secured/admin/groups/delete", name="OpitNotesUserBundle_admin_groups_delete")
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */
    public function deleteGroupAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $groupId = $this->getRequest()->request->get('id');
        $userRelatedGroup = array();
        
        if (!is_array($groupId)) {
            $groupId = array($groupId);
        }
        foreach ($groupId as $id) {
            $group = $entityManager->getRepository('OpitNotesUserBundle:Groups')->find($id);
            if (0 === count($group->getUsers())) {
                $entityManager->remove($group);
            } else {
                $userRelatedGroup[] = $group->getName();
            }
        }
        
        $entityManager->flush();
        
        $group = $this->getDoctrine()->getRepository('OpitNotesUserBundle:Groups')->findAll();
        
        if (count($userRelatedGroup) > 0) {
            return new JsonResponse(array('userRelated' => $userRelatedGroup));
        }
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig', $this->getAllGroups());
    }
    
    /**
     * @Route("/secured/admin/teams/list", name="OpitNotesUserBundle_admin_teams_list")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function teamsListAction()
    {
        return $this->render('OpitNotesUserBundle:Admin:teamsList.html.twig', $this->getAllTeams());
    }
    
    /**
     * @Route("/secured/admin/teams/show/{id}", name="OpitNotesUserBundle_admin_teams_show", requirements={ "id" = "new|\d+"})
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */
    public function teamsShowAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $teamId = $request->attributes->get('id');
        
        if ('new' === $teamId) {
            $team = new Teams();
        } else {
            $team = $entityManager->getRepository('OpitNotesUserBundle:Teams')->find($teamId);
        }
        
        $team->setTeamName($request->request->get('value'));
        $entityManager->persist($team);

        $entityManager->flush();
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig', $this->getAllTeams());
    }
    
    /**
     * @Route("/secured/admin/teams/delete", name="OpitNotesUserBundle_admin_teams_delete")
     * @Secure(roles="ROLE_ADMIN")
     * @Method({"POST"})
     * @Template()
     */
    public function deleteTeamAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $teamId = $this->getRequest()->request->get('id');
        
        if (!is_array($teamId)) {
            $teamId = array($teamId);
        }
        foreach ($teamId as $id) {
            $team = $entityManager->getRepository('OpitNotesUserBundle:Teams')->find($id);
            if (0 === count($team->getEmployees())) {
                $entityManager->remove($team);
            }
        }
        
        $entityManager->flush();
        
        return $this->render('OpitNotesUserBundle:Shared:_list.html.twig', $this->getAllTeams());
    }
    
    /**
     * Get all groups(roles) and return an array of results
     * 
     * @return array
     */
    protected function getAllGroups()
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
    
    protected function getAllTeams()
    {
        $numberOfRelations = array();
        $teams = $this->getDoctrine()->getRepository('OpitNotesUserBundle:Teams')->findAll();
        
        foreach ($teams as $team) {
            $numberOfRelations[$team->getId()] = count($team->getEmployees());
        }
        
        return array(
            'propertyNames' => array('id', 'teamName'),
            'propertyValues' => $teams,
            'numberOfRelations' => $numberOfRelations
        );
    }
}
