<?php

namespace VKC\DataHub\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use VKC\DataHub\UserBundle\Document\User;
use VKC\DataHub\UserBundle\Form\Type\UserFormType;

/**
 * @Route("/users")
 * @Security("has_role('ROLE_ADMIN')")
 */
class UsersController extends Controller
{
    const ENTITY_NAME = 'VKCDataHubUserBundle:User';

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        $result = $dm->createQueryBuilder(static::ENTITY_NAME)
            ->sort('enabled', 'DESC')
            ->sort('username', 'ASC')
            ->getQuery()
            ->execute();

        return array(
            'entities' => $result,
        );
    }

    /**
     * @Route("/new")
     * @Template()
     */
    public function newAction(Request $request)
    {
        $entity = new User();
        $form = $this->createForm(UserFormType::class, $entity);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity->setEnabled(false);

            $password = uniqid('', true);
            $entity->setPlainPassword($password);

            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->persist($entity);
            $dm->flush();

            $entity->setEnabled(false);
            $entity->setConfirmationToken($this->get('fos_user.util.token_generator')->generateToken());
            $this->container->get('fos_user.mailer')->sendConfirmationEmailMessage($entity);

            $dm->flush();

            return $this->redirectToRoute('vkc_datahub_user_users_show', ['id' => $entity->getId()]);
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

        $form = $this->createForm(UserFormType::class, $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            // Override the password if needed
            $password = $form->has('plainPassword') ? $form->get('plainPassword')->getData() : null;

            if (!empty($password)) {
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($entity);
                $entity->setPassword($encoder->encodePassword($password, $entity->getSalt()));
            }

            $dm = $this->get('doctrine_mongodb')->getManager();
            $dm->flush();

            return $this->redirectToRoute('vkc_datahub_user_users_show', ['id' => $entity->getId()]);
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
        // $dm->remove($entity);
        $entity->setEnabled(false);
        $dm->flush();

        return $this->redirectToRoute('vkc_datahub_user_users_index');
    }
}
