<?php

namespace DataHub\UserBundle\Controller;

use DataHub\UserBundle\Controller\InstallerControllerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Event\FormEvent;
use DataHub\UserBundle\Form\InstallerCreateFormType;
use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\DTO\ProfileCreateData;

class InstallerController extends controller implements InstallerControllerInterface
{
    /**
     * @Route("/install/administrator", name="datahub_user_install_admin")
     */
    public function registerAction(Request $request)
    {
        // Redirect back to dashboard if a superadministrator already exists
        // Prevent creation of multiple superadministrators.
        $userRepository = $this->get('datahub.security.user.repository');
        if ($userRepository->getSuperAdmin()) {
            $url = $this->generateUrl('datahub_shared_default_index');
            return new RedirectResponse($url);
        }

        $user = new User();
        $dispatcher = $this->get('event_dispatcher');

        $assembler = $this->get('datahub.security.user.dto.profile_create_assembler');
        $profileCreateData = $assembler->createDTO($user);

        $form = $this->createForm(InstallerCreateFormType::class, $profileCreateData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $event = new FormEvent($form, $request);

            // @todo
            //   Change the event to something else
            //   Register existing listeners to new event.
            //   Add installer event to add roles and random password for administrator.
            $dispatcher->dispatch(DataHubUserEvents::INSTALLATION_SUCCESS, $event);

            $user = $assembler->updateProfile($user, $profileCreateData);

            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $documentManager->persist($user);
            $documentManager->flush();

            $this->addFlash('success', 'Superadministrator '.$user->getUsername(). ' created successfully. An email was send to '.$user->getEmail());

            $url = $this->generateUrl('datahub_shared_default_index');
            return new RedirectResponse($url);
        }

        return $this->render(
            '@DataHubUser/Installer/installer.create.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Create an administrator.',
            ]
        );
    }
}