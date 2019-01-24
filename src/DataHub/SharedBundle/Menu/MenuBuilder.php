<?php

namespace DataHub\SharedBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MenuBuilder
{
    private $factory;

    /**
     * Constructor
     * 
     * @param FactoryInterface $factory MenuFactory
     * @param AuthorizationCheckerInterface $authChecker Authentication checker
     */
    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage)
    {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Defines the profile menu
     * 
     * @param Array $array
     */
    public function createProfileMenu(array $options) {
        $menu = $this->factory->createItem('root');

        if ($this->authChecker->isGranted('ROLE_CONSUMER') !== false) {
            $user = $this->tokenStorage->getToken()->getUser();

            $menu->addChild(
                $user->getUsername(), 
                array(
                    'route' => 'datahub_user_users_show',
                    'routeParameters' => array('username' => $user->getUsername())
                )
            );
            $menu[$user->getUserName()]->setLinkAttribute('class', 'logged-in-user');

            $menu->addChild('Logout', array('route' => 'security_logout'));
            $menu['Logout']->setLinkAttribute('class', 'logout');
        } else {
            $menu->addChild('Login', array('route' => 'security_login'));
            $menu['Login']->setLinkAttribute('class', 'login');
        }

        $menu->setChildrenAttribute('class', 'nav navbar-nav navbar-right');

        return $menu;
    }

    /**
     * Defines the MainMenu menu
     * 
     * @param Array $array
     */
    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Dashboard', array('route' => 'datahub_shared_default_index'));

       if ($this->authChecker->isGranted('ROLE_ADMIN') !== false) {
            $menu->addChild('Administration', array('route' => 'datahub_user_users_index'));

            $menu['Administration']->setChildrenAttribute('class', 'list-group');
            $menu['Administration']->setLinkAttribute('class', 'admin-administration');

            $menu['Administration']->addChild('Users', array('route' => 'datahub_user_users_index', 'attributes' => array('class' => 'list-group-item')));
            $menu['Administration']['Users']->setLinkAttribute('class', 'admin-users');

            $menu['Administration']->addChild('OAuth Clients', array('route' => 'datahub_oauth_clients_index', 'attributes' => array('class' => 'list-group-item')));
            $menu['Administration']['OAuth Clients']->setLinkAttribute('class', 'admin-oauth-clients');
       } 

        $menu->addChild('REST API', array('route' => 'nelmio_api_doc_index'));
        $menu['REST API']->setLinkAttribute('class', 'docs-rest-api');
        
        $menu->addChild('OAI-PMH', array('route' => 'datahub_static_docs_oai'));
        $menu['OAI-PMH']->setLinkAttribute('class', 'docs-oai-pmh');

        $menu->setChildrenAttribute('class', 'nav navbar-nav main-nav');

        return $menu;
    }
}
