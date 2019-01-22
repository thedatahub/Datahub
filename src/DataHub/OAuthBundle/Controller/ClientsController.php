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
use DataHub\OAuthBundle\Form\ClientCreateFormType;
use DataHub\OAuthBundle\Form\ClientDeleteFormType;

/**
 * @Route("/clients")
 * @Security("has_role('ROLE_ADMIN')")
 */
class ClientsController extends Controller
{
    const ENTITY_NAME = 'DataHubOAuthBundle:Client';

    /**
     * @Route("/", name="datahub_oauth_clients_index")
     * @Security("is_granted('ROLE_ADMIN')")
     * @Template()
     */
    public function indexAction()
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $result = $dm->getRepository(static::ENTITY_NAME)->findAll();

        return array(
            'clients' => $result,
        );
    }

    /**
     * @Route("/client/{applicationName}", name="datahub_oauth_clients_show")
     */
    public function showAction(Request $request, $applicationName)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $client = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findOneBy(['applicationName' => $applicationName]);

        if (!$client) {
            throw $this->createNotFoundException();
        }

        $clientOwner = $client->getUser();

        if ($currentUser->getId() !== $clientOwner->getId()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        return $this->render(
            '@DataHubOAuth/Client/client.html.twig',
            [
                'client' => $client,
            ]
        );
    }

    /**
     * @Route("/add", name="datahub_oauth_clients_add")
     * @Security("is_granted('ROLE_CONSUMER')")
     */
    public function addAction(Request $request)
    {
        $client = new Client();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $assembler = $this->get('datahub.oauth.client.dto.client_create_assembler');
        $clientCreateData = $assembler->createDTO($client);

        $form = $this->createForm(ClientCreateFormType::class, $clientCreateData);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $client = $assembler->updateProfile($client, $clientCreateData);

            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $documentManager->persist($client);
            $documentManager->flush();

            $this->addFlash('success', 'OAuth client ' . $client->getApplicationName() . ' created successfully.');

            return $this->redirectToRoute(
                'datahub_user_users_show', 
                array(
                    'username' => $user->getUsername()
                )
            );
        }

        return $this->render(
            '@DataHubOAuth/Client/client.create.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Create a new client',
            ]
        );
    }

    /**
     * @Route("/{id}/edit")
     * @Template()
     */
    public function editAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository(static::ENTITY_NAME)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ClientFormType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->flush();

            return $this->redirectToRoute('datahub_oauth_clients_show', ['id' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }

    /**
     * @Route("/{applicationName}/delete")
     */
    public function deleteAction(Request $request, $applicationName)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $client = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findOneBy([
                 'applicationName' => $applicationName,
                 'user' => $currentUser
            ]);

        if (!$client) {
            throw $this->createNotFoundException();
        }

        $clientOwner = $client->getUser();

        if ($currentUser->getId() !== $clientOwner->getId()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $form = $this->createForm(ClientDeleteFormType::class, $client);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->getClickedButton() && 'deleteClientBtn' === $form->getClickedButton()->getName()) {
                $documentManager = $this->get('doctrine_mongodb')->getManager();
                $documentManager->remove($client);
                $documentManager->flush();
    
                $this->addFlash('success', 'Client ' . $client->getApplicationName() . ' removed successfully.');
            }

            return $this->redirectToRoute('datahub_user_users_show', array('username' => $clientOwner->getUserName()));
        }

        return $this->render(
            '@DataHubOAuth/Client/client.delete.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Delete OAuth client',
            ]
        );
    }
}
