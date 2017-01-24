<?php

namespace DataHub\OAIBundle\Controller;

use DataHub\OAIBundle\Repository\Repository;
use Picturae\OaiPmh\Provider;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Zend\Diactoros\Response;
use Psr\Http\Message\ServerRequestInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * OAI Endpoint controller for Data.
 * @package DataHub\ResourceAPIBundle
 */
class ProviderController extends Controller
{
		/**
		 * @Route("/")
		 */
		public function indexAction(ServerRequestInterface $psrRequest) {
				$request = $this->getRequest();

				$repository =  $this->get('datahub.oai.repository');
				$provider = new Provider($repository);
				$provider->setRequest($psrRequest);
				$psrResponse = $provider->getResponse();

				return $psrResponse;
		}
}
