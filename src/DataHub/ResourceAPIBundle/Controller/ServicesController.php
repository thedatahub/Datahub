<?php

namespace DataHub\ResourceAPIBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use DataHub\ResourceAPIBundle\Form\Type\DataVisualizeServiceFormType;

/**
 * REST controller for Services.
 *
 * @author  Kalman Olah <kalman@inuits.eu>
 * @package DataHub\ResourceAPIBundle
 */
class ServicesController extends Controller
{
    /**
     * Get a resource map.
     *
     * @ApiDoc(
     *     section = "Resources",
     *     resource = true,
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         404 = "Returned if the resource was not found"
     *     }
     * )
     *
     * @Annotations\Get("/services/resource_maps/{id}", requirements={"id" = "[a-zA-Z0-9-]+"})
     *
     * @Annotations\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @param integer $id ID of entry to return
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function getResourceMapServiceAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        $dataConverters = $this->get('datahub.resource.data_converters');

        $data = [];

        return [
            'count'   => count($data),
            'results' => $data,
        ];
    }

    /**
     * Visualize a piece of data.
     *
     * @ApiDoc(
     *     section = "Resources",
     *     resource = true,
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         404 = "Returned if the resource was not found",
     *         400 = "Returned if the form could not be validated"
     *     }
     * )
     *
     * @Annotations\Get("/services/data_raw/{id}", requirements={"id" = "[a-zA-Z0-9-]+"})
     *
     * @Annotations\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @param integer $id ID of entry to return
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function getRawDataServiceAction(ParamFetcherInterface $paramFetcher, Request $request, $id)
    {
        $oauthUtils = $this->get('datahub.oauth.oauth');
        $dataManager = $this->get('datahub.resource.data')->setOwnerId($oauthUtils->getClient()->getId());
        $dataConverters = $this->get('datahub.resource.data_converters');

        $entity = $dataManager->getData($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        return new Response($entity['data']['raw']);
    }
}
