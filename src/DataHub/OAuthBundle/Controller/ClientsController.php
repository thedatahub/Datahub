<?php

namespace DataHub\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use DataHub\OAuthBundle\Document\Client;

class ClientsController extends Controller
{
    /**
     * @Route("/clients", name="datahub_oauth_clients_index")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {   
        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $clients = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findAll();

        return $this->render(
            '@DataHubOAuth/Clients/index.html.twig',
            [
                'clients' => $clients,
            ]
        );
    }
}