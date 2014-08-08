<?php

/*
 *  This file is part of the OPIT-HRM project.
 *
 *  (c) Opit Consulting Kft. <info@opit.hu>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\OpitHrm\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\OpitHrm\UserBundle\Form\UserShowType;
use Opit\OpitHrm\UserBundle\Form\ChangePasswordType;
use Opit\OpitHrm\UserBundle\Entity\User;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Opit\Component\Utils\Utils;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

/**
 * Description of UserController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package OPIT-HRM
 * @subpackage UserBundle
 */
class UserController extends Controller
{
    /**
     * @Route("/secured/user/list", name="OpitOpitHrmUserBundle_user_list")
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

        $groups = $entityManager->getRepository('OpitOpitHrmUserBundle:Groups');
        $request = $this->getRequest();
        $showList = $request->request->get('showList');
        $isSearch = (bool) $request->request->get('issearch');
        $offset = $request->request->get('offset');
        $config = $this->container->getParameter('pager_config');
        $templateVars = array();

        if ($isSearch) {
            $templateVars['isSearch'] = true;

            $allRequests = $request->request->all();

            $users = $entityManager->getRepository('OpitOpitHrmUserBundle:User')
                    ->findUsersByPropertyUsingLike($allRequests, ($offset * $config['max_results']), $config['max_results']);
        } else{
            $users = $entityManager->getRepository('OpitOpitHrmUserBundle:User')
                ->getPaginaton(($offset * $config['max_results']), $config['max_results']);
        }

        foreach ($users as $user) {
            //fetch roles for the user
            $localUserRoles = $groups->findUserGroupsArray($user->getId());
            $roles = array();

            //get all user roles and put them in an array
            foreach ($localUserRoles as $role) {
                $roles[] = $role['name'];
            }

            //calculate employee annual leave entitlement
            if($user->getEmployee()->getEntitledLeaves()){
                $templateVars['leaveEntitlement'][$user->getId()] = $user->getEmployee()->getEntitledLeaves();
            }else{
                $leaveCalculationService = $this->get('opit_opithrm_leave.leave_calculation_service');
                $templateVars['leaveEntitlement'][$user->getId()] = $leaveCalculationService->leaveDaysCalculationByEmployee($user->getEmployee());
            }
        }

        $templateVars['numberOfPages'] = ceil(count($users) / $config['max_results']);
        $templateVars['maxPages'] = $config['max_pages'];
        $templateVars['users'] = $users;

        if (!$request->request->get('incrementOffset')) {
            $templateVars['offset'] = $offset + 1;
        } else {
            $templateVars['offset'] = $offset;
        }

        if (null === $showList && (null === $offset && !$isSearch)) {
            $template = 'OpitOpitHrmUserBundle:User:list.html.twig';
        } else {
            $template = 'OpitOpitHrmUserBundle:User:_list.html.twig';
        }

        return $this->render($template, $templateVars);
    }

    /**
     * To generate add/edit item form
     *
     * @Route("/secured/user/show/{id}", name="OpitOpitHrmUserBundle_user_show", requirements={"id" = "\d+"})
     * @Secure(roles="ROLE_SYSTEM_ADMIN")
     * @Method({"GET"})
     * @Template()
     */
    public function showUserFormAction()
    {
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $securityContext = $this->container->get('security.context');

        if ($id) {
            $user = $this->getUserObject($id);
        } else {
            $user = new User();
        }

        // Check if the current user has permission to view the object
        if ($id && !$securityContext->isGranted(BasicPermissionMap::PERMISSION_VIEW, $user)) {
            throw new AccessDeniedException('Access denied');
        }

        $form = $this->createForm(
            new UserShowType($this->container),
            $user
        );
        return $this->render('OpitOpitHrmUserBundle:User:showUserForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * To add/edit user in OPIT-HRM
     *
     * @Route("/secured/user/add/{id}", name="OpitOpitHrmUserBundle_user_add", requirements={"id" = "\d+"})
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
        $aclManager = $this->container->get('opit.security.acl.manager');
        $userService = $this->get('opit.model.user');
        $user = ($request->attributes->get('id')) ? $this->getUserObject($request->attributes->get('id')) : new User();
        $isNew = null === $user->getId();

        // Check if the current user has permission to edit the object
        if (!$isNew && !$securityContext->isGranted(BasicPermissionMap::PERMISSION_EDIT, $user)) {
            throw new AccessDeniedException('Access denied');
        }

        // User object permission is based on assigned roles
        // Minimum permission is equal to ROLE_SYSTEM_ADMIN, any higher roles in the hierachy require ROLE_ADMIN
        // As per definition, a system admin can only set roles lower than his highest role in the hierachy
        $postedValues = $request->request->get('user');
        $securityRole = 'ROLE_SYSTEM_ADMIN';
        if (isset($postedValues['groups'])) {
            $roles = $em->getRepository('OpitOpitHrmUserBundle:Groups')->findById($postedValues['groups']);
            foreach ($roles as $role) {
                if (in_array($role->getRole(), array('ROLE_ADMIN', 'ROLE_SYSTEM_ADMIN', 'ROLE_GENERAL_MANAGER', 'ROLE_TEAM_MANAGER'))) {
                    $securityRole = 'ROLE_ADMIN';
                    break;
                }
            }
        }

        $form = $this->createForm(new UserShowType($this->container), $user);

        if ($request->isMethod("POST")) {
            $userRoles = $user->getRoles();
            $form->handleRequest($request);

            // Process form data and create user
            if ($form->isValid()) {

                if ($isNew) {
                    $user->setIsFirstLogin(true);
                    $user->setPassword($userService->encodePassword($user));
                    $userService->sendNewPasswordMail($user);
                }

                // Save the user.
                $em->persist($user);
                $em->flush();

                if ($user->getRoles() != $userRoles) {
                    // Add or update owner access to user object
                    $role = $em->getRepository('OpitOpitHrmUserBundle:Groups')
                        ->findOneByRole($securityRole);

                    $aclManager->revokeAll($user);
                    $aclManager->grant($user, $role);
                }

                $result['response'] = 'success';

                    return $this->listAction();
            } else {
                $statusCode = 500;
                $errors = Utils::getErrorMessages($form);
                $result['errorMessage'] = $errors;
            }

        }
        return new JsonResponse(array($result), $statusCode);
    }

    /**
     * To delete user in OPIT-HRM
     *
     * @Route("/secured/user/delete", name="OpitOpitHrmUserBundle_user_delete")
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
                $id = (int) $id;
                // If the logged in user is not equal to deleting user then remove it.
                if ($loggedInUserId !== (int) $id) {
                    $user = $this->getUserObject($id);

                    // Check if the current user has permission to delete the object
                    if (!$securityContext->isGranted(BasicPermissionMap::PERMISSION_DELETE, $user)) {
                        throw new AccessDeniedException('Access denied');
                    }

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
     * @Route("/secured/user/password/reset", name="OpitOpitHrmUserBundle_user_password_reset")
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
            ->getRepository('OpitOpitHrmUserBundle:User')
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
     * @Route("/secured/user/show/password/{id}", name="OpitOpitHrmUserBundle_user_show_password", requirements={"id" = "\d+"})
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
            'OpitOpitHrmUserBundle:User:_changePasswordForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Change password on first login action
     *
     * @Route("/secured/user/changepassword", name="OpitOpitHrmUserBundle_user_change_password")
     * @Secure(roles="ROLE_USER")
     * @Method({"POST", "GET"})
     * @Template()
     */
    public function changePasswordAction()
    {
        $request = $this->getRequest();
        $user = $this->container->get('security.context')->getToken()->getUser();

        if (!$user->getIsFirstLogin()) {
            return $this->redirect($this->generateUrl('OpitOpitHrmUserBundle_security_login'));
        }

        $form = $this->createForm(new ChangePasswordType(true), $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setIsFirstLogin(0);
                $this->setUserPassword($user);

                return $this->redirect($this->generateUrl('OpitOpitHrmUserBundle_user_show_infoboard'));
            }
        }

        return $this->render(
            'OpitOpitHrmUserBundle:User:changePasswordForm.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Change the password of an exist user.
     *
     * @Route("/secured/user/update/password/{id}", name="OpitOpitHrmUserBundle_user_update_password", requirements={"id" = "\d+"})
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
     * @Route("/secured/user/ldap/enabled", name="OpitOpitHrmUserBundle_user_ldap_enabled")
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
     * @Route("/secured/user/search/{role}", name="OpitOpitHrmUserBundle_user_search", defaults={"role"=false})
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
                ->getRepository('OpitOpitHrmUserBundle:User')
                ->findTeamManagersUsingLike($currentUser, $term, $role);
        } else {
            $users = $this->getDoctrine()
                ->getRepository('OpitOpitHrmUserBundle:User')
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

        if (!$user = $em->getRepository('OpitOpitHrmUserBundle:User')->find($id)) {
            throw $this->createNotFoundException('User object with id "'.$id.'" not found.');
        }

        return $user;
    }

    /**
     * Set password for the user
     *
     * @param Opit\OpitHrm\UserBundle\Entity\User $user
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
     * @Route("/secured/user/show/infoboard", name="OpitOpitHrmUserBundle_user_show_infoboard")
     * @Secure(roles="ROLE_USER")
     * @Method({"GET"})
     * @Template()
     */
    public function showUserSummaryAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $this->render('OpitOpitHrmUserBundle:User:showUserSummary.html.twig', array('employee' => $user->getEmployee()));
    }
}
