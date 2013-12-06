<?php

namespace Opit\Notes\UserBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Opit\Notes\UserBundle\Form\UserShowType;
use Opit\Notes\UserBundle\Entity\User;

class UserController extends Controller
{
    protected $jsRoutes;
    
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
        $request = $this->getRequest();
        $showList = (boolean) $request->request->get('showList');

        // urls for js scripts
        $this->jsRoutes = $this->generateJsRoutes();
        
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
            array("propertyNames" => $propertyNames, "propertyValues" => $propertyValues, 'urls' => $this->jsRoutes)
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
        
        return $this->render(
            'OpitNotesUserBundle:Shared:_list.html.twig',
            array("propertyNames" => $propertyNames, "propertyValues" => $propertyValues)
        );
    }

    /**
     * To generate add/edit item form
     *
     * @Route("/secured/user/show", name="OpitNotesUserBundle_user_show")
     * @Method({"POST"})
     * @Template()
     */
    public function showUserFormAction()
    {
        $request = $this->getRequest();
        $userId =  (integer) $request->request->get('userId');
        $user = null;

        // If this is an edit request.
        if ($userId > 0) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('OpitNotesUserBundle:User')->find($userId);
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
     * @Route("/secured/user/add", name="OpitNotesUserBundle_user_add")
     * @Method({"POST"})
     */
    public function addUserAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $request = $this->getRequest();
        $userData =  $request->request->get('user');
        $userId = $userData['userId'];
        $result = array();

        if ($userId) {
            $user = $em->getRepository('OpitNotesUserBundle:User')->find($userId);
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
        $originalPassword = $user->getPassword();
        $form->handleRequest($request);

        // Process form data and create user
        if ($form->isValid()) {

            $plainPassword = $userData['password'];

            if (!empty($plainPassword)) {
                // Encode the user's password.
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $newPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($newPassword);
            } else {
                $user->setPassword($originalPassword);
            }
            // Save the user.
            $em->persist($user);
            $em->flush();
            $result['response'] = 'success';
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($user);

        if (count($errors) > 0) {
            $errorsString = "";

            foreach ($errors as $e) {
                $errorsString .= $e->getMessage();
            }

            $result['errorMessage'] = $errorsString;
            $result['response'] = 'error';
        }
        return new JsonResponse(array($result));
    }

    /**
     * To add/edit user in Notes
     *
     * @Route("/secured/user/delete", name="OpitNotesUserBundle_user_delete")
     * @Method({"POST"})
     */
    public function deleteResourceAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();
        $userIds = $request->request->get('userIds');

        $users = $em->getRepository('OpitNotesUserBundle:User')->findUsersUsingIn($userIds);

        foreach ($users as $user) {
            $em->remove($user);
        }

        $em->flush();

        $deleteMessage = 'success';

        return new JsonResponse(array('code' => 200, 'response' => $deleteMessage));
    }

    /**
     * Generates notes routes for use in js scripts
     *
     * @return array Genrated notes routes collection
     */
    protected function generateJsRoutes()
    {
        $router = $this->container->get('router');

        $js_routes = array();
        foreach ($router->getRouteCollection()->all() as $name => $route) {
            if (strpos($name, 'OpitNotesUserBundle') !== false) {
                $js_routes[$name] = $this->generateUrl($name);
            }
        }

        return $js_routes;
    }
}
