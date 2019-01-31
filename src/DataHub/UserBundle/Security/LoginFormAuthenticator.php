<?php

namespace DataHub\UserBundle\Security;

use DataHub\UserBundle\Form\LoginForm;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $formFactory;

    private $passwordEncoder;

    private $router;

    private $dm;

    public function __construct(FormFactoryInterface $formFactory, DocumentManager $dm, RouterInterface $router, UserPasswordEncoder $passwordEncoder)
    {
        $this->formFactory = $formFactory;
        $this->dm = $dm;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getCredentials(Request $request)
    {
        $isLoginSubmit = $request->attributes->get('_route') === 'security_login' && $request->isMethod('POST');

        if (!$isLoginSubmit) {
            return;
        }

        $form = $this->formFactory->create(LoginForm::class);
        $form->handleRequest($request);

        $data = $form->getData();

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $data['_username']
        );

        return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $username = $credentials['_username'];

        $found = $this->dm->getRepository('DataHubUserBundle:User')
            ->findOneBy(['username' => $username]);

        if (!$found) {
            throw new CustomUserMessageAuthenticationException(
                'Those credentials are not valid.'
            );
        }

        if ($found->getEnabled() === false) {
            throw new CustomUserMessageAuthenticationException(
                'Your account is inactive and needs be activated.'
            );
        }

        return $found;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $password = $credentials['_password'];
        
        if ($this->passwordEncoder->isPasswordValid($user, $password)) {
            return true;
        }

        throw new CustomUserMessageAuthenticationException(
            'Those credentials are not valid.'
        );
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('security_login');
    }

    protected function getDefaultSuccessRedirectUrl()
    {
        return $this->router->generate('datahub_shared_default_index');
    }
}
