<?php

namespace DataHub\SharedBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilder
{
    private $factory;

    /**
     * Constructor
     * 
     * @param FactoryInterface $factory MenuFactory
     * @param AuthorizationCheckerInterface $authChecker Authentication checker
     */
    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker)
    {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
    }

    /**
     * Defines the profile menu
     * 
     * @param Array $array
     */
    public function createProfileMenu(array $options) {
        $menu = $this->factory->createItem('root');
        
        $menu->addChild('Login', array('route' => 'fos_user_security_login'));

        if ($this->authChecker->isGranted('ROLE_USER') !== false) {
            unset($menu['Login']);
            $menu->addChild('Logout', array('route' => 'fos_user_security_logout'));
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

        if ($this->authChecker->isGranted('ROLE_USER') !== false) {
            $menu->addChild('OAuth', array('route' => 'datahub_oauth_clients_index'));

            $menu['OAuth']->addChild('Clients', array('route' => 'datahub_oauth_clients_index', 'attributes' => array('class' => 'list-group-item')));
            $menu['OAuth']->addChild('Tokens', array('route' => 'datahub_oauth_tokens_index', 'attributes' => array('class' => 'list-group-item')));

            $menu->addChild('Users', array('route' => 'datahub_user_users_index'));
            $menu['Users']->addChild('Users', array('route' => 'datahub_user_users_index', 'attributes' => array('class' => 'list-group-item')));
        } 

        $menu->addChild('REST API', array('route' => 'nelmio_api_doc_index'));
        $menu->addChild('OAI-PMH', array('route' => 'datahub_static_docs_oai'));

        $menu->setChildrenAttribute('class', 'nav navbar-nav main-nav');

        return $menu;
    }
}
