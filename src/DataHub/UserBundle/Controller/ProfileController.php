<?php

namespace DataHub\UserBundle\Controller;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Event\FormEvent;
use DataHub\UserBundle\Form\ProfileCreateFormType;
use DataHub\UserBundle\Form\ProfileEditFormType;
use DataHub\UserBundle\Form\ProfileDeleteForm;
use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\DTO\ProfileCreateData;
use DataHub\UserBundle\DTO\ProfileEditData;

class ProfileController extends Controller
{
    /**
     * @Route("/profile/{username}", name="datahub_user_users_show")
     * @Template()
     */
    public function showAction(Request $request, $username)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $user = $documentManager
            ->getRepository('DataHubUserBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($currentUser->getUsername() !== $username) {
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
     * @Route("/profile/{username}/edit", name="datahub_user_users_edit")
     */
    public function editAction(Request $request, $username)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $user = $documentManager
            ->getRepository('DataHubUserBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($currentUser->getUsername() !== $username) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $assembler = $this->get('datahub.security.user.dto.profile_edit_assembler');
        $profileEditData = $assembler->createDTO($user);

        $form = $this->createForm(
            ProfileEditFormType::class, $profileEditData, [
                'create'      => false,
            ]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {

            $user = $assembler->updateProfile($user, $profileEditData);

            $documentManager->persist($user);
            $documentManager->flush();

            $this->addFlash('success', 'User '.$user->getUsername(). ' was edited successfully.');

            return $this->redirectToRoute('datahub_user_users_show', array('username' => $user->getUsername()));
        }

        return $this->render(
            '@DataHubUser/Profile/profile.edit.form.html.twig',
            [
                'form'      => $form->createView(),
                'title'     => 'Edit user ' . $user->getUserName(),
            ]
        );
    }

    /**
     * @Route("/profile/{username}/delete", name="datahub_user_users_delete")
     * @Template()
     */
    public function deleteAction(Request $request, $username)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $user = $documentManager
            ->getRepository('DataHubUserBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        // Administrator should not be able to delete own account.
        if ($currentUser->getUsername() == $username) {
            $authChecker = $this->get('security.authorization_checker');
            if ($authChecker->isGranted('ROLE_ADMIN')) {
                throw $this->createAccessDeniedException();
            }
        }

        if ($currentUser->getUsername() !== $username) {
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
        $dispatcher = $this->get('event_dispatcher');

        $assembler = $this->get('datahub.security.user.dto.profile_create_assembler');
        $profileCreateData = $assembler->createDTO($user);

        $form = $this->createForm(ProfileCreateFormType::class, $profileCreateData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(DataHubUserEvents::REGISTRATION_SUCCESS, $event);

            $user = $assembler->updateProfile($user, $profileCreateData);

            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $documentManager->persist($user);
            $documentManager->flush();

            $this->addFlash('success', 'User '.$user->getUsername(). ' created successfully.');

            return $this->redirectToRoute('datahub_user_users_index');
        }

        return $this->render(
            '@DataHubUser/Profile/profile.create.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Create a new user',
            ]
        );
    }
}
