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
use Opit\Notes\UserBundle\Form\UserShowType;
use Opit\Notes\UserBundle\Form\ChangePasswordType;
use Opit\Notes\UserBundle\Entity\User;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Component\Utils\Utils;
use Symfony\Component\Finder\Exception\AccessDeniedException;

/**
 * Description of UserController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage UserBundle
 */
class UserController extends Controller
{
    /**
     * @Route("/secured/user/list", name="OpitNotesUserBundle_user_list")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Template()
     */
    public function listAction()
    {
        //get all rows from database table
        $entityManager = $this->getDoctrine()->getManager();
        /* TODO: Add special behavior for admin users to view and re-enable users
        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('ROLE_SYSTEM_ADMIN')) {
            $filters = $entityManager->getFilters();
            $filters->disable('softdeleteable');
        }*/

        $groups = $entityManager->getRepository('OpitNotesUserBundle:Groups');
        $propertyValues = array();
        $request = $this->getRequest();
        $showList = $request->request->get('showList');
        $isSearch = (bool) $request->request->get('issearch');
        $offset = $request->request->get('offset');
        $config = $this->container->getParameter('pager_config');
        $templateVars = array();

        if ($isSearch) {
            $allRequests = $request->request->all();

            $users = $entityManager->getRepository('OpitNotesUserBundle:User')
                    ->findUsersByPropertyUsingLike($allRequests, ($offset * $config['max_results']), $config['max_results']);
        } else{
            $users = $entityManager->getRepository('OpitNotesUserBundle:User')
                ->getPaginaton(($offset * $config['max_results']), $config['max_results']);
        }

        foreach ($users as $user) {
            //fetch roles for the user
            $localUserRoles = $groups->findUserGroupsArray($user->getId());
            $roles = array();

            //get all user roles and put them in an array
            foreach ($localUserRoles as $role) {
                $roles[] = $role["name"];
            }

            $employeeName = $user->getEmployee() ? $user->getEmployee()->getEmployeeName() : '';
            //calculate employee annual leave entitlement
            if($user->getEmployee()->getEntitledLeaves()){
                $empLeaveEntitlement = $user->getEmployee()->getEntitledLeaves();
                $templateVars['leaveEntitlement'] = true;
            }else{
                $leaveCalculationService = $this->get('opit_notes_leave.leave_calculation_service');
                $empLeaveEntitlement = $leaveCalculationService->leaveDaysCalculationByEmployee($user->getEmployee());
                $templateVars['leaveEntitlement'] = true;
            }

            //create new array for user containing its properties
            $propertyValues[$user->getId()] = array(
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'employeeName' => $employeeName,
                'isActive' => $user->getIsActive(),
                'ldapEnabled' => $user->isLdapEnabled(),
                'roles' => $roles,
                'allowedToEdit' => $this->isSystemAdminAllowedToEdit($user->getId()),
                'leaveEntitlement' => $empLeaveEntitlement
            );
        }

        $numberOfPages = ceil(count($users) / $config['max_results']);
        // Used by _list template. Alias is needed for odering but cut of for displaying
        $propertyNames = array("u.username", "u.email", "e.employeeName", "u.isActive", "u.ldapEnabled", "u.roles");

        $templateVars['numberOfPages'] = $numberOfPages;
        $templateVars['maxPages'] = $config['max_pages'];
        if (!$request->request->get('incrementOffset')) {
            $templateVars['offset'] = $offset + 1;
        } else {
            $templateVars['offset'] = $offset;
        }
        $templateVars['propertyNames'] = $propertyNames;
        $templateVars['propertyValues'] = $propertyValues;

        if (null === $showList && (null === $offset && !$isSearch)) {
            $template = 'OpitNotesUserBundle:User:list.html.twig';
        } else {
            $template = 'OpitNotesUserBundle:Shared:_list.html.twig';
        }

        return $this->render($template, $templateVars);
    }

    /**
     * To generate add/edit item form
     *
     * @Route("/secured/user/show/{id}", name="OpitNotesUserBundle_user_show", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"GET"})
     * @Template()
     */
    public function showUserFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');

        if ($id) {
            $user = $this->getUserObject($id);
        } else {
            $user = new User();
        }

        $form = $this->createForm(
            new UserShowType($this->container),
            $user
        );
        return $this->render('OpitNotesUserBundle:User:showUserForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * To add/edit user in Notes
     *
     * @Route("/secured/user/add/{id}", name="OpitNotesUserBundle_user_add", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"POST"})
     * @throws AccessDeniedException
     */
    public function addUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $result = array('response' => 'error');
        $statusCode = 200;
        $errors = array();
        $securityContext = $this->container->get('security.context');
        $isSystemAdmin = $securityContext->isGranted('ROLE_SYSTEM_ADMIN');
        $userService = $this->get('opit.model.user');
        $id = $isSystemAdmin ? $request->attributes->get('id') : $this->get('security.context')->getToken()->getUser()->getId();
        $systemAdminGroups = array();

        $user = ($id) ? $this->getUserObject($request->attributes->get('id')) : new User();

        $form = $this->createForm(
            new UserShowType($this->container),
            $user
        );

        // Check if user is system admin
        if (null !== $user->getId() && $this->isSystemAdmin($securityContext)) {
            $systemAdminGroups = $this->getSystemAdminGroups($user->getId());
        }

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if(null !== $user->getId() && !$this->isSystemAdminAllowedToEdit($user->getId())) {
                throw new AccessDeniedException(
                    'Access denied for user.'
                );
            }

            // Process form data and create user
            if ($form->isValid()) {

                if (null === $user->getId()) {
                    $user->setIsFirstLogin(true);
                    $user->setPassword($userService->encodePassword($user));
                    $userService->sendNewPasswordMail($user);
                }

                foreach ($systemAdminGroups as $systemAdminGroup) {
                    $user->addGroup(current($systemAdminGroup));
                }

                // Save the user.
                $em->persist($user);
                $em->flush();

                $result['response'] = 'success';

                if ($isSystemAdmin && $request->headers->get('referer') === $this->generateUrl('OpitNotesUserBundle_user_list', array(), true)) {
                    return $this->listAction();
                }
            } else {
                $statusCode = 500;
                $errors = Utils::getErrorMessages($form);
                $result['errorMessage'] = $errors;
            }

        }
        return new JsonResponse(array($result), $statusCode);
    }

    /**
     * To delete user in Notes
     *
     * @Route("/secured/user/delete", name="OpitNotesUserBundle_user_delete")
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"POST"})
     */
    public function deleteUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-user');
        $result = array('response' => 'error');

        // Get the logged in user.
        $securityContext = $this->container->get('security.context');
        $token = $securityContext->getToken();
        $loggedInUserId = $token->getUser()->getId();

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        try {
            foreach ($ids as $id) {
                // If the logged in user is not equal to deleting user then remove it.
                if ($loggedInUserId !== (int) $id) {
                    if(!$this->isSystemAdminAllowedToEdit($id)) {
                        throw new AccessDeniedException(
                            'Access denied for user.'
                        );
                    }

                    $user = $this->getUserObject($id);
                    $em->remove($user);
                }
            }
            $em->flush();
            $result['response'] = 'success';

        } catch (Exception $ex) {
             $result['errorMessage'] = $ex->getMessage();
        }
        return new JsonResponse(array('code' => 200, $result));
    }

    /**
     * Method to change password for user
     *
     * @Route("/secured/user/password/reset", name="OpitNotesUserBundle_user_password_reset")
     * @Secure(roles="ROLE_USER")
     * @Method({"POST"})
     */
    public function resetPasswordAction()
    {
        $request = $this->getRequest();
        $entityManager = $this->getDoctrine()->getManager();
        $userService = $this->get('opit.model.user');
        $userId = $request->request->get('id');
        $user =  $this->getDoctrine()->getManager()
            ->getRepository('OpitNotesUserBundle:User')
            ->find($userId);
        $user->setIsFirstLogin(true);
        $user->setPassword($userService->encodePassword($user));
        $userService->sendNewPasswordMail($user, true);
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse('');
    }

    /**
     * To generate change password form
     *
     * @Route("/secured/user/show/password/{id}", name="OpitNotesUserBundle_user_show_password", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_USER")
     * @Method({"GET"})
     * @Template()
     */
    public function showChangePasswordAction()
    {
        $request = $this->getRequest();

        $user = $this->getUserObject($request->attributes->get('id'));

        $form = $this->createForm(new ChangePasswordType(), $user);

        return $this->render(
            'OpitNotesUserBundle:User:_changePasswordForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Change password on first login action
     *
     * @Route("/secured/user/changepassword", name="OpitNotesUserBundle_user_change_password")
     * @Secure(roles="ROLE_USER")
     * @Method({"POST", "GET"})
     * @Template()
     */
    public function changePasswordAction()
    {
        $request = $this->getRequest();
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (!$user->getIsFirstLogin()) {
            return $this->redirect($this->generateUrl('OpitNotesUserBundle_security_login'));
        }

        $form = $this->createForm(new ChangePasswordType(true), $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setIsFirstLogin(0);
                $this->setUserPassword($user);

                return $this->redirect($this->generateUrl('OpitNotesUserBundle_user_show_infoboard'));
            }
        }

        return $this->render(
            'OpitNotesUserBundle:User:changePasswordForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Change the password of an exist user.
     *
     * @Route("/secured/user/update/password/{id}", name="OpitNotesUserBundle_user_update_password", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_USER")
     * @Method({"POST"})
     * @Template()
     */
    public function updatePasswordAction()
    {
        $result = array('response' => 'error');
        $request = $this->getRequest();
        $statusCode = 200;

        $user = $this->getUserObject($request->attributes->get('id'));

        $form = $this->createForm(new ChangePasswordType(), $user);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->setUserPassword($user);
                $result['response'] = 'success';
            } else {
                $statusCode = 500;
                $errors = Utils::getErrorMessages($form);
                $result['errorMessage'] = $errors;
            }
        }

        return new JsonResponse(array($result), $statusCode);
    }

    /**
     * Returns if the given user has ldap auth enabled
     *
     * @Route("/secured/user/ldap/enabled", name="OpitNotesUserBundle_user_ldap_enabled")
     * @Secure(roles="ROLE_USER")
     * @Method({"POST"})
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function isLdapUser()
    {
        $user = $this->getUserObject();

        return new JsonResponse(array('ldap_enabled' => $user->isldapEnabled()));
    }

    /**
     * Finds users called through ajax requests for autocomplete forms
     *
     * Softdeleteable filter has to be active for this action to ensure
     * only present users will be found.
     *
     * @Route("/secured/user/search/{role}", name="OpitNotesUserBundle_user_search", defaults={"role"=false})
     * @Secure(roles="ROLE_USER")
     * @Method({"POST"})
     */
    public function userSearchAction()
    {
        $userNames = array();
        $request = $this->getRequest();
        $term = $request->request->get('term');
        $role = $request->attributes->get('role');

        if(null !== $request->request->get('roles')) {
            $role = $request->request->get('roles');
        }

        if (false === $role) {
            $userService = $this->get('opit.model.user');
            $role = $userService->getInheritedRoles($this->getUser());
        }

        if ('role_team_manager' === $role) {
            $currentUser = $this->container->get('security.context')->getToken()->getUser();

            // Find the team managers which are in the same team as the employee.
            $users = $this->getDoctrine()
                ->getRepository('OpitNotesUserBundle:User')
                ->findTeamManagersUsingLike($currentUser, $term, $role);
        } else {
            $users = $this->getDoctrine()
                ->getRepository('OpitNotesUserBundle:User')
                ->findUserByEmployeeNameUsingLike($term, $role);
        }

        foreach ($users as $user) {
            $userUniqueIdentifier = $user->getEmployee()->getEmployeeNameFormatted();
            $userNames[] = array(
                'value' => $userUniqueIdentifier,
                'label' => $userUniqueIdentifier,
                'id'=>$user->getId()
            );
        }

        return new JsonResponse($userNames);
    }

    /**
     * Gets a user object
     *
     * @param integer $id
     * @return object A user object
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getUserObject($id = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        if (null === $id) {
            $id = $request->request->get('id');
        }

        if (!$user = $em->getRepository('OpitNotesUserBundle:User')->find($id)) {
            throw $this->createNotFoundException('User object with id "'.$id.'" not found.');
        }

        return $user;
    }

    /**
     * Set password for the user
     *
     * @param Opit\Notes\UserBundle\Entity\User $user
     */
    protected function setUserPassword($user)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $newPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($newPassword);

        // Save the user.
        $entityManager->persist($user);
        $entityManager->flush();
    }

   /**
     * To show logged in  user summary page.Seperate bundles should add there info directly into the template.
     *
     * @Route("/secured/user/show/infoboard", name="OpitNotesUserBundle_user_show_infoboard")
     * @Secure(roles="ROLE_USER")
     * @Method({"GET"})
     * @Template()
     */
    public function showUserSummaryAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('OpitNotesUserBundle:User:showUserSummary.html.twig', array('employee' => $user->getEmployee()));
    }

    protected function isSystemAdmin($securityContext)
    {
        return $securityContext->isGranted('ROLE_SYSTEM_ADMIN') && !$securityContext->isGranted('ROLE_ADMIN');
    }

    protected function isSystemAdminAllowedToEdit($userId)
    {
        $securityContext = $this->container->get('security.context');
        $entityManager = $this->getDoctrine()->getManager();
        $groups = $entityManager->getRepository('OpitNotesUserBundle:Groups');
        $userRoles = $groups->findUserGroupsArray($userId);

        if (null !== $userId && $this->isSystemAdmin($securityContext) && $securityContext->getToken()->getUser()->getId() != $userId) {
            foreach ($userRoles as $role) {
                if (in_array($role['role'], array('ROLE_ADMIN', 'ROLE_GENERAL_MANAGER', 'ROLE_TEAM_MANAGER'))) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function getSystemAdminGroups($userId)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $systemAdminGroups = array();

        // Get system admins all assigned groups
        $systemAdminAssignedGroups = $this->getDoctrine()->getManager()->getRepository('OpitNotesUserBundle:Groups')->findUserGroupsArray($userId);
        // Assing role system admin
        $systemAdminGroups[] = $entityManager->getRepository('OpitNotesUserBundle:Groups')->findByRole('ROLE_SYSTEM_ADMIN');
        // Loop through all roles system admin has got
        foreach ($systemAdminAssignedGroups as $systemAdminGroup) {
            // If role is gm or tm add it to the system admin groups array
            if ('ROLE_TEAM_MANAGER' === $systemAdminGroup['role'] || 'ROLE_GENERAL_MANAGER' === $systemAdminGroup['role']) {
                $systemAdminGroups[] = $entityManager->getRepository('OpitNotesUserBundle:Groups')->findByRole($systemAdminGroup['role']);
            }
        }

        return $systemAdminGroups;
    }
}
