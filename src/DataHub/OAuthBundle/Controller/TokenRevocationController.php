<?php

namespace DataHub\OAuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class TokenRevocationController extends Controller
{
    /**
     * @Route("/v2/revoke")
     * @Method("GET|POST|DELETE")
     */
    public function revokeAction(Request $request)
    {
        $token = $request->get('token', null);

        if ($token) {
            $dm = $this->get('doctrine_mongodb')->getManager();

            foreach (array('AccessToken', 'RefreshToken') as $tokenType) {
                $entity = $dm->createQueryBuilder("DataHubOAuthBundle:{$tokenType}")
                    ->field('token')->equals($token)
                    ->getQuery()
                    ->getSingleResult();

                if ($entity) {
                    $dm->remove($entity);
                    $dm->flush();

                    return new JsonResponse([
                        'result'  => 'success',
                        'message' => $this->get('translator')->trans('The token has been revoked.'),
                    ]);
                }
            }
        }

        throw $this->createNotFoundException();
    }
}
