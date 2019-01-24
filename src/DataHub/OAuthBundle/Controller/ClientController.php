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
use DataHub\OAuthBundle\Form\ClientEditFormType;
use DataHub\OAuthBundle\Form\ClientDeleteFormType;
use DataHub\OAuthBundle\Form\ClientRevokeFormType;

class ClientController extends Controller
{

    /**
     * @Route("/client/{externalId}", name="datahub_oauth_client_show")
     */
    public function showAction(Request $request, $externalId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $client = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findOneBy(['externalId' => $externalId]);

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
     * @Route("/add", name="datahub_oauth_client_add")
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
     * @Route("/client/{externalId}/edit", name="datahub_oauth_client_edit"))
     */
    public function editAction(Request $request, $externalId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $client = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findOneBy([
                 'externalId' => $externalId
            ]);

        if (!$client) {
            throw $this->createNotFoundException();
        }

        $clientOwner = $client->getUser();

        if ($currentUser->getId() !== $clientOwner->getId()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $assembler = $this->get('datahub.oauth.client.dto.client_edit_assembler');
        $clientEditData = $assembler->createDTO($client);

        $form = $this->createForm(
            ClientEditFormType::class, $clientEditData
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $client = $assembler->updateProfile($client, $clientEditData);

            $documentManager = $this->get('doctrine_mongodb')->getManager();
            $documentManager->persist($client);
            $documentManager->flush();

            $this->addFlash('success', 'OAuth client ' . $client->getApplicationName() . ' was successfully updated.');

            return $this->render(
                '@DataHubOAuth/Client/client.html.twig',
                [
                    'client' => $client,
                ]
            );
        }

        return $this->render(
            '@DataHubOAuth/Client/client.edit.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Edit an OAuth client',
            ]
        );
    }

    /**
     * @Route("/{externalId}/delete", name="datahub_oauth_client_delete")
     */
    public function deleteAction(Request $request, $externalId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $client = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findOneBy([
                 'externalId' => $externalId,
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

                return $this->redirectToRoute('datahub_user_users_show', array('username' => $clientOwner->getUserName()));
            }

            if ($form->getClickedButton() && 'cancelDeleteClientBtn' === $form->getClickedButton()->getName()) {
                return $this->redirectToRoute('datahub_oauth_client_show', array('externalId' => $client->getExternalid()));
            }
        }

        return $this->render(
            '@DataHubOAuth/Client/client.delete.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Delete OAuth client',
            ]
        );
    }

    /**
     * @Route("/{externalId}/revoke", name="datahub_oauth_client_revoke_tokens")
     */
    public function revokeTokensAction(Request $request, $externalId)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
    
        $currentUser = $this->getUser();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $client = $documentManager
             ->getRepository('DataHubOAuthBundle:Client')
             ->findOneBy([
                 'externalId' => $externalId,
            ]);

        if (!$client) {
            throw $this->createNotFoundException();
        }

        $clientOwner = $client->getUser();

        if ($currentUser->getId() !== $clientOwner->getId()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $currentUser, 'Unable to access this page!');
        }

        $form = $this->createForm(ClientRevokeFormType::class, $client);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->getClickedButton() && 'revokeTokensBtn' === $form->getClickedButton()->getName()) {
                $accessTokens = $documentManager
                    ->getRepository('DataHubOAuthBundle:AccessToken')
                    ->findBy([
                        'client' => $client,
                    ]);

                foreach ($accessTokens as $token) {
                    $documentManager->remove($token);
                    $documentManager->flush();
                }
    
                $refreshTokens = $documentManager
                    ->getRepository('DataHubOAuthBundle:RefreshToken')
                    ->findBy([
                        'client' => $client,
                    ]);

                foreach ($refreshTokens as $token) {
                    $documentManager->remove($token);
                    $documentManager->flush();
                }

                $this->addFlash('success', 'Client ' . $client->getApplicationName() . ' revoked tokens successfully.');
            }

            return $this->redirectToRoute('datahub_user_users_show', array('username' => $clientOwner->getUserName()));
        }

        return $this->render(
            '@DataHubOAuth/Client/client.revoke.form.html.twig',
            [
                'form' => $form->createView(),
                'title' => 'Revoke OAuth tokens for this client',
            ]
        );
    }
}
