<?php

namespace DataHub\UserBundle\EventListener;

use DataHub\UserBundle\Document\User;
use DataHub\UserBundle\Controller\InstallerController;
use DataHub\UserBundle\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InstallerListener
{
    private $resolver;

    private $userRepository;

    /**
     * Constructor
     * 
     * @param ControllerResolverInterface $resolver Instance of ControllerResolver
     * @param UserRepository $userRepository Instance of UserRepository
     * 
     * @return void
     */
    public function __construct(ControllerResolverInterface $resolver, UserRepository $userRepository)
    {
        $this->resolver = $resolver;
        $this->userRepository = $userRepository;
    }

    /**
     * Callback for the kernel.controller event.
     *
     * Triggers a redirect to an installer form when no users exists. Indicates
     * a first time use of the application and prompts the user to create an 
     * administrator user.
     * 
     * @param FilterControllerEvent $event Controller Event.
     * 
     * @return void
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        
        $user = $this->userRepository->getAdmin();
        if ($user instanceof User) {
            return;
        }

        if (!($controller[0] instanceof InstallerController)) {
            $fakeRequest = $event->getRequest()->duplicate(null, null, array('_controller' => 'DataHubUserBundle:Registration:register'));
            $controller = $this->resolver->getController($fakeRequest);
            $event->setController($controller);
        }
    }
}
