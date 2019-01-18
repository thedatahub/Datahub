<?php

namespace DataHub\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use DataHub\UserBundle\Form\LoginForm;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     * @Template()
     */
    public function loginAction()
    {
        // Redirect logged in users back to the dashboard
        $securityContext = $this->container->get('security.authorization_checker');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('datahub_shared_default_index');
        }
        
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            '_username' => $lastUsername,
        ]);

        return array(
            // last username entered by the user
            'form' => $form->createView(),
            'error' => $error,
        );
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logoutAction()
    {
        throw new \Exception("This should not be reached");
    }
}
