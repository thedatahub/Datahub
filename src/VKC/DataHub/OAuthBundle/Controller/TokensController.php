<?php

namespace VKC\DataHub\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use VKC\DataHub\OAuthBundle\Document\Client;
use VKC\DataHub\OAuthBundle\Form\Type\ClientFormType;

/**
 * @Route("/tokens")
 * @Security("has_role('ROLE_ADMIN')")
 */
class TokensController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $authCodes = $dm->createQueryBuilder('VKCDataHubOAuthBundle:AuthCode')
            ->sort('expiresAt', 'ASC')
            ->getQuery()
            ->execute();

        $accessTokens = $dm->createQueryBuilder('VKCDataHubOAuthBundle:AccessToken')
            ->sort('expiresAt', 'ASC')
            ->getQuery()
            ->execute();

        $refreshTokens = $dm->createQueryBuilder('VKCDataHubOAuthBundle:RefreshToken')
            ->sort('expiresAt', 'ASC')
            ->getQuery()
            ->execute();

        $clients = [];

        foreach ([$authCodes, $accessTokens, $refreshTokens] as $data) {
            foreach ($data as $d) {
                if ($client = $d->getClient()) {
                    $clients[(string) $client->getId()] = $client;
                }
            }
        }

        return [
            'clients'       => array_values($clients),
            'authCodes'     => $authCodes,
            'accessTokens'  => $accessTokens,
            'refreshTokens' => $refreshTokens,
        ];
    }

    /**
     * @Route("/{type}/{id}/revoke", requirements={"type"="client|auth_code|access_token|refresh_token"})
     * @Template()
     */
    public function revokeAction($type, $id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $user = $this->getUser();

        $entity_type = str_replace(' ', '', ucwords(str_replace('_', ' ', $type)));
        $entity = $dm->getRepository("VKCDataHubOAuthBundle:{$entity_type}")->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        if ($type == 'client') {
            foreach (['AuthCode', 'AccessToken', 'RefreshToken'] as $tokenType) {
                $entities = $dm->createQueryBuilder("VKCDataHubOAuthBundle:{$tokenType}")
                    ->field('client')->references($entity)
                    ->getQuery()
                    ->execute();

                foreach ($entities as $e) {
                    $dm->remove($e);
                }
            }
        } else {
            $dm->remove($entity);
        }

        $dm->flush();

        return $this->redirectToRoute('vkc_datahub_oauth_tokens_index');
    }
}
