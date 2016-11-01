<?php

namespace DataHub\ResourceAPIBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

use DataHub\ResourceAPIBundle\Form\Type\DataFormType;

/**
 * REST controller for Data converters.
 *
 * @author  Kalman Olah <kalman@inuits.eu>
 * @package DataHub\ResourceAPIBundle
 */
class DataConvertersController extends Controller
{
    /**
     * List data converters.
     *
     * @ApiDoc(
     *     section = "Resources",
     *     resource = true,
     *     statusCodes = {
     *       200 = "Returned when successful"
     *     }
     * )
     *
     * @Annotations\Get("/data/converters")
     *
     * @Annotations\View(
     *     serializerGroups={"list"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     *
     * @return array<mixed>
     */
    public function cgetDataConvertersAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        $dataConverters = $this->get('datahub.resource.data_converters');
        $data = $dataConverters->getConverterList();

        return [
            'count'   => count($data),
            'results' => $data,
        ];
    }
}
