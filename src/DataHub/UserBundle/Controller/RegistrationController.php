<?php

namespace DataHub\UserBundle\Controller;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DataHub\UserBundle\Event\FilterUserResponseEvent;
use DataHub\UserBundle\DataHubUserEvents;
use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\DTO\ProfileEditData;

class RegistrationController extends Controller
{
    /**
     * @Route("/registration/confirmation/{token}", name="datahub_user_registration_confirmation")
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

        $documentManager->persist($user);
        $documentManager->flush();

        $url = $this->generateUrl('datahub_user_registration_confirmed');
        $response = new RedirectResponse($url);

        $event = new FilterUserResponseEvent($user, $request, $response);
        $dispatcher->dispatch(DataHubUserEvents::REGISTRATION_CONFIRMED, $event);

        return $response;
    }

    /**
     * @Route("/registration/confirmed", name="datahub_user_registration_confirmed")
     */

    public function confirmedAction(Request $request)
    {
        $user = $this->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render(
            '@DataHubUser/Registration/confirmed.html.twig',
            [
                'user' => $user,
            ]
        );
    }
}