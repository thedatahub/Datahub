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
use DataHub\OAuthBundle\Form\Type\ClientFormType;

/**
 * @Route("/clients")
 * @Security("has_role('ROLE_ADMIN')")
 */
class ClientsController extends Controller
{
    const ENTITY_NAME = 'DataHubOAuthBundle:Client';

    /**
     * @Route("/")
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
     * @Route("/new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new Client();
        $form = $this->createForm(ClientFormType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $dm = $this->get('doctrine_mongodb')->getManager();
            $currentUser = $this->getUser();
            $entity->setUser($currentUser);
            $dm->persist($entity);
            $dm->flush();

            return $this->redirectToRoute('datahub_oauth_clients_show', ['id' => $entity->getId()]);
        }

        return [
            'entity' => $entity,
            'form'   => $form->createView(),
        ];
    }


    /**
     * @Route("/{id}/show")
     * @Template()
     */
    public function showAction($id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository(static::ENTITY_NAME)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        return [
            'entity' => $entity,
        ];
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
     * @Route("/{id}/delete")
     * @Method("POST|GET")
     */
    public function deleteAction(Request $request, $id)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $entity = $dm->getRepository(static::ENTITY_NAME)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        $dm->remove($entity);
        $dm->flush();

        return $this->redirectToRoute('datahub_oauth_clients_index');
    }
}
