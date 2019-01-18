<?php

namespace DataHub\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AdminController extends Controller
{
    /**
     * @Route("/users", name="datahub_user_users_index")
     * @Template()
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $recordRepository = $this->get('datahub.security.user.repository');
        $users = $recordRepository->getAll();

        return [
            'users' => $users
        ];
    }
}
