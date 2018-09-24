<?php

namespace DataHub\UserBundle\Controller;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DataHub\UserBundle\Form\ProfileForm;
use DataHub\UserBundle\Form\ProfileDeleteForm;
use DataHub\UserBundle\Document\User;

class ProfileController extends Controller
{
    /**
     * @Route("/profile/{id}", name="datahub_user_users_show")
     * @Template()
     */
    public function showAction(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $user = $documentManager->getRepository('DataHubUserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($currentUser->getId() !== $id) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $clients = $documentManager
            ->getRepository('DataHubOAuthBundle:Client')
            ->findBy(['user' => $user]);

        return $this->render(
            '@DataHubUser/Profile/profile.html.twig',
            [
                'clients' => $clients,
                'user' => $user,
            ]
        );
    }

    /**
     * @Route("/profile/{id}/edit", name="datahub_user_users_edit")
     */
    public function editAction(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $user = $documentManager->getRepository('DataHubUserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($currentUser->getId() !== $id) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $form = $this->createForm(
            ProfileForm::class, $user, [
                'create'      => false, 
                'submitLabel' => 'Update user'
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            // PlainPassword is not a field watched from Doctrine. So we have to trigger preUpdate manually
            $eventManager = $documentManager->getEventManager();
            $eventArgs = new LifecycleEventArgs($user, $documentManager);
            $eventManager->dispatchEvent(\Doctrine\ODM\MongoDB\Events::preUpdate, $eventArgs);

            $documentManager->persist($user);
            $documentManager->flush();

            $this->addFlash('success', 'User '.$user->getUsername(). ' was edited successfully.');

            return $this->redirectToRoute('datahub_user_users_show', array('id' => $user->getId()));
        }

        return $this->render(
            '@DataHubUser/Profile/profile.form.html.twig',
            [
                'form'      => $form->createView(),
                'title'     => 'Edit user',
            ]
        );
    }

    /**
     * @Route("/profile/{id}/delete", name="datahub_user_users_delete")
     * @Template()
     */
    public function deleteAction(Request $request, $id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $user = $documentManager->getRepository('DataHubUserBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($currentUser->getId() !== $id) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $form = $this->createForm(ProfileDeleteForm::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->getClickedButton() && 'deleteUserBtn' === $form->getClickedButton()->getName()) {
                $documentManager = $this->get('doctrine_mongodb')->getManager();
                $documentManager->remove($user);
                $documentManager->flush();
    
                $this->addFlash('success', 'User '.$user->getUsername(). ' removed successfully.');
            }

            return $this->redirectToRoute('datahub_user_users_index');
        }

        return $this->render(
            '@DataHubUser/Profile/profile.delete.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Delete user',
            ]
        );
    }

    /**
     * @Route("/add", name="datahub_user_users_add")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function addAction(Request $request)
    {      
        $user = new User();
        $form = $this->createForm(ProfileForm::class, $user);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $documentManager->persist($user);
            $documentManager->flush();

            $this->addFlash('success', 'User '.$user->getUsername(). ' created successfully.');

            return $this->redirectToRoute('datahub_user_users_index');
        }

        return $this->render(
            '@DataHubUser/Profile/profile.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Create a new user',
            ]
        );
    }
}
