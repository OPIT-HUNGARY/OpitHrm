<?php

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

class UserController extends Controller
{
    /**
     * @Route("/secured/user/list", name="OpitNotesUserBundle_user_list")
     * @Secure(roles="ROLE_ADMIN")
     * @Template()
     */
    public function listAction()
    {
        //get all rows from database table
        $entityManager = $this->getDoctrine()->getManager();
        /* TODO: Add special behavior for admin users to view and re-enable users
        $securityContext = $this->get('security.context');
        if ($securityContext->isGranted('ROLE_ADMIN')) {
            $filters = $entityManager->getFilters();
            $filters->disable('softdeleteable');
        }*/
        
        $users = $entityManager->getRepository('OpitNotesUserBundle:User')->findAll();
        $groups = $entityManager->getRepository('OpitNotesUserBundle:Groups');
        $propertyValues = array();
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');

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

        return $this->render(
            $showList ? 'OpitNotesUserBundle:Shared:_list.html.twig' : 'OpitNotesUserBundle:User:list.html.twig',
            array("propertyNames" => $propertyNames, "propertyValues" => $propertyValues)
        );
    }
    
    /**
    * @Route("/secured/user/search", name="OpitNotesUserBundle_user_search")
    * @Template()
    * @Method({"POST"})
    */
    public function searchAction()
    {
        $request = $this->getRequest()->request->all();
        $empty = array_filter($request, function ($value) {
            return !empty($value);
        });

        if (array_key_exists('resetForm', $request) || empty($empty)) {
            return $this->listAction();
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
        return $this->render(
            'OpitNotesUserBundle:Shared:_list.html.twig',
            array("propertyNames" => $propertyNames, "propertyValues" => $propertyValues)
        );
    }

    /**
     * To generate add/edit item form
     *
     * @Route("/secured/user/show/{id}", name="OpitNotesUserBundle_user_show", requirements={"id" = "\d+"})
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
            new UserShowType(
                $this->getDoctrine()->getEntityManager(),
                $this->container->getParameter('notes.user.status')
            ),
            $user
        );
        return $this->render('OpitNotesUserBundle:User:showUserForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * To add/edit user in Notes
     *
     * @Route("/secured/user/add/{id}", name="OpitNotesUserBundle_user_add", requirements={"id" = "\d+"})
     * @Method({"POST"})
     */
    public function addUserAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $id = $request->attributes->get('id');
        $errorMessages = array();
        $result = array('response' => 'error');
        $statusCode = 200;

        if ($id) {
            $user = $this->getUserObject($request->attributes->get('id'));
        } else {
            $user = new User();
        }

        $form = $this->createForm(
            new UserShowType(
                $this->getDoctrine()->getEntityManager(),
                $this->container->getParameter('notes.user.status')
            ),
            $user
        );

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);
            // Process form data and create user
            if ($form->isValid()) {

                if (!$user->getId()) {
                    // Encode the user's password.
                    $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                    $newPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                    $user->setPassword($newPassword);
                }
                // Save the user.
                $em->persist($user);
                $em->flush();
                $result['response'] = 'success';
            }
            $validator = $this->get('validator');
            $errors = $validator->validate($user);
            $formData = $request->request->get('user');

            if (isset($formData['password']) && $formData['password']['password'] != $formData['password']['confirm']) {
                $errorMessages[] = 'The passwords do not match.';
            }

            if (count($errors) > 0) {
               foreach ($errors as $e) {
                    $errorMessages[] = $e->getMessage();
                }
                $statusCode = 500;
            }
            $result['errorMessage'] = $errorMessages;
        }
        return new JsonResponse(array($result), $statusCode);
    }

    /**
     * To delete user in Notes
     *
     * @Route("/secured/user/delete", name="OpitNotesUserBundle_user_delete")
     * @Method({"POST"})
     */
    public function deleteUserAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $ids = (array) $request->request->get('delete-user');
        $result = array('response' => 'error');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        try {
            foreach ($ids as $id) {
                $user = $this->getUserObject($id);
                $em->remove($user);
            }
            $em->flush();
            $result['response'] = 'success';

        } catch (Exception $ex) {
             $result['errorMessage'] = $ex->getMessage();
        }
        return new JsonResponse(array('code' => 200, $result));
    }

    /**
     * To generate change password form
     *
     * @Route("/secured/user/show/password/{id}", name="OpitNotesUserBundle_user_show_password", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @Template()
     */
    public function showChangePasswordAction()
    {
        $request = $this->getRequest();

        $user = $this->getUserObject($request->attributes->get('id'));

        $form = $this->createForm(new ChangePasswordType(), $user);

        return $this->render('OpitNotesUserBundle:User:_changePasswordForm.html.twig', array('form' => $form->createView()));
    }

    /**
     * Change the password of an exist user.
     *
     * @Route("/secured/user/update/password/{id}", name="OpitNotesUserBundle_user_update_password", requirements={"id" = "\d+"})
     * @Method({"POST"})
     * @Template()
     */
    public function updatePasswordAction()
    {
        $errorMessages = array();
        $result = array('response' => 'error');
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUserObject($request->attributes->get('id'));

        $form = $this->createForm(new ChangePasswordType(), $user);

        if ($request->isMethod("POST")) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $newPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($newPassword);

                // Save the user.
                $em->persist($user);
                $em->flush();
                $result['response'] = 'success';
            }
            $validator = $this->get('validator');
            $errors = $validator->validate($user);
            $formData = $request->request->get('user');

            if ($formData['password']['first'] != $formData['password']['second']) {
                $errorMessages[] = 'The passwords do not match.';
            }
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
}
