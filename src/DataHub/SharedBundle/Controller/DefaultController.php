<?php

namespace DataHub\SharedBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $recordsRepository = $recordRepository = $this->get('datahub.resource_api.repository.default');

        return [
             'documentCount' => $recordsRepository->count()
        ];
    }
}
