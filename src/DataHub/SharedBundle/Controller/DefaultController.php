<?php

namespace DataHub\SharedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Route("/admin", name="datahub_shared_default_admin")
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }
}
