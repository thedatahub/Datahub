<?php

namespace DataHub\UserBundle\Controller;

use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Event\FilterUserResponseEvent;
use DataHub\UserBundle\Event\FilterUserEvent;
use DataHub\UserBundle\Form\RequestPasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ResettingController extends Controller
{
    /**
     * @Route("/resetting/request", name="datahub_user_resetting_request")
     */
    public function requestAction(Request $request)
    {
        $dispatcher = $this->get('event_dispatcher');
        $assembler = $this->get('datahub.security.user.dto.resetpassword_create_assembler');
        $requestPasswordData = $assembler->createDTO();
        $form = $this->createForm(RequestPasswordFormType::class, $requestPasswordData);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $email = $requestPasswordData->getEmail();
            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $user = $documentManager
                ->getRepository('DataHubUserBundle:User')
                ->findOneBy(array('email' => $email));

            $user = $assembler->updateProfile($user, $requestPasswordData);

            $event = new FilterUserEvent($form, $request, $user);
            $dispatcher->dispatch(DataHubUserEvents::RESET_SUCCESS, $event);
    
            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $documentManager->flush();
    
            $this->addFlash('success', 'An email with a reset link has been sent to ' . $email);
        }

        return $this->render(
            '@DataHubUser/Resetting/reset.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Request a new password',
                'error' => '',
            ]
        );
    }

    /**
     * @Route("/resetting/{token}", name="datahub_user_resetting_confirmation")
     */
    public function confirmAction(Request $request, $token)
    {
        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $dispatcher = $this->get('event_dispatcher');
        
        $user = $documentManager
            ->getRepository('DataHubUserBundle:User')
            ->findByConfirmationToken($token);

        if (null == $user) {
            return new RedirectResponse($this->container->get('router')->generate('security_login'));
        }

        $assembler = $this->get('datahub.security.user.dto.profile_edit_assembler');
        $profileEditData = $assembler->createDTO($user);

        $profileEditData->setConfirmationToken(null);
        $profileEditData->setEnabled(true);

        $user = $assembler->updateProfile($user, $profileEditData);

        $documentManager->flush();

        $this->addFlash('success', 'Please reset your password!');
        $url = $this->generateUrl('datahub_user_users_edit', array('username' => $user->getUsername()));
        $response = new RedirectResponse($url);

        $event = new FilterUserResponseEvent($user, $request, $response);
        $dispatcher->dispatch(DataHubUserEvents::RESET_CONFIRMED, $event);

        return $response;
    }
}
