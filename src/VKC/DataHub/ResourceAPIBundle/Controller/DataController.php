<?php

namespace VKC\DataHub\ResourceAPIBundle\Controller;

use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Doctrine\ORM\Tools\Pagination\Paginator;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

use VKC\DataHub\ResourceAPIBundle\Form\Type\DataFormType;

/**
 * REST controller for Data.
 *
 * @author  Kalman Olah <kalman@inuits.eu>
 * @package VKC\DataHub\ResourceAPIBundle
 */
class DataController extends Controller
{
    /**
     * List data.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     statusCodes = {
     *       200 = "Returned when successful"
     *     }
     * )
     *
     * @Annotations\Get("/data")
     *
     * @Annotations\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing entries.")
     * @Annotations\QueryParam(name="limit", requirements="\d{1,2}", default="5", description="How many entries to return.")
     * @Annotations\QueryParam(name="sort", requirements="[a-zA-Z\.]+,(asc|desc|ASC|DESC)", nullable=true, description="Sorting field and direction.")
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
    public function cgetDatasAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        $offset = intval($paramFetcher->get('offset'));
        $limit = intval($paramFetcher->get('limit'));

        $oauthUtils = $this->get('vkc.datahub.oauth.oauth');
        $dataManager = $this->get('vkc.datahub.resource.data')->setOwnerId($oauthUtils->getClient()->getId());

        $data = $dataManager->cgetData($offset, $limit);

        return $data;
    }

    /**
     * Get a single data.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         404 = "Returned if the resource was not found"
     *     }
     * )
     *
     * @Annotations\Get("/data/{id}", requirements={"id" = "[a-zA-Z0-9-]+"})
     *
     * @Annotations\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @param string $id Data ID of entry to return
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function getDataAction(ParamFetcherInterface $paramFetcher, Request $request, $id)
    {
        $logger = $this->get('logger');
        $logger->info('GET data: ' . $id);

        $oauthUtils = $this->get('vkc.datahub.oauth.oauth');
        $dataManager = $this->get('vkc.datahub.resource.data')->setOwnerId($oauthUtils->getClient()->getId());

        $entity = $dataManager->getData('data.administrativeMetadata.recordWrap.recordID._', $id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        // if everything is configured correctly there should be a matching converter for the provided content type
        $converter = $this->get('vkc.datahub.resource.data_converters')->getConverter('lidoxml');

        $data = $converter->fromArray($entity['data']);
        //$data = $entity['raw'];

        return new Response($data, Response::HTTP_OK);
    }

    /**
     * Create a data.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     input = "VKC\DataHub\ResourceAPIBundle\Form\Type\DataFormType",
     *     statusCodes = {
     *         201 = "Returned when successful",
     *         400 = "Returned if the form could not be validated"
     *     }
     * )
     *
     * @Annotations\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true,
     *     statusCode=201
     * )
     *
     * @Annotations\Post("/data")
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     *
     * @return mixed
     *
     * @throws BadRequestHttpException if the request doesn't have all arguments
     */
    public function postDataAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        $logger = $this->get('logger');
        $logger->debug('POST data');

        $oauthUtils = $this->get('vkc.datahub.oauth.oauth');
        $dataManager = $this->get('vkc.datahub.resource.data')->setOwnerId($oauthUtils->getClient()->getId());

        // if everything is configured correctly there should be a matching converter for the provided content type
        $converter = $this->get('vkc.datahub.resource.data_converters')->getConverter($request->getContentType());

        // convert the content
        try {
            $data = $converter->toArray($request->getContent());
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException(strtr('Invalid {format}: {error}', [
                'format' => $request->getContentType(),
                'error'  => $e->getMessage(),
            ]));
        }

        $entity = $dataManager->createData($data, $request->getFormat($request->getContentType()), $request->getContent());
        $entity['_id'] = (string) $entity['_id'];
        $pid = $data[0]['administrativeMetadata'][0]['recordWrap']['recordID'][0]['_'];

        $logger->info('Created data:' . $pid);

        $logger->debug($request->getRequestUri());
        $logger->debug($request->getUri());
        $logger->debug($request->getUriForPath('data/' . $pid));

        return new Response('', Response::HTTP_CREATED, ['Location' => $request->getRequestUri() . '/' . $pid]);
    }

    /**
     * Update a data (replaces the entire resource).
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     input = "VKC\DataHub\ResourceAPIBundle\Form\Type\DataFormType",
     *     statusCodes = {
     *         204 = "Returned when successful",
     *         400 = "Returned if the form could not be validated",
     *         404 = "Returned if the resource was not found"
     *     }
     * )
     *
     * @Annotations\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true,
     *     statusCode=204
     * )
     *
     * @Annotations\Put("/data/{id}", requirements={"id" = "[a-zA-Z0-9-]+"})
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @param integer $id ID of entry to update
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function putDataAction(ParamFetcherInterface $paramFetcher, Request $request, $id)
    {
        $logger = $this->get('logger');
        $logger->debug('PUT data');

        $oauthUtils = $this->get('vkc.datahub.oauth.oauth');
        $dataManager = $this->get('vkc.datahub.resource.data')->setOwnerId($oauthUtils->getClient()->getId());

        $id_path = 'data.administrativeMetadata.recordWrap.recordID._';

        $entity = $dataManager->getData($id_path, $id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        // if everything is configured correctly there should be a matching converter for the provided content type
        $converter = $this->get('vkc.datahub.resource.data_converters')->getConverter($request->getContentType());

        // convert the content
        try {
            $data = $converter->toArray($request->getContent());
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException(strtr('Invalid {format}: {error}', [
                'format' => $request->getContentType(),
                'error'  => $e->getMessage(),
            ]));
        }

        $entity = $dataManager->updateData($id_path, $id, $data, $request->getFormat($request->getContentType()), $request->getContent());
        $entity['_id'] = (string) $entity['_id'];
        $pid = $data[0]['administrativeMetadata'][0]['recordWrap']['recordID'][0]['_'];

        $logger->info('Updated data:' . $pid);

        if (!$entity) {
            throw new HttpException('The record could not be updated.');
        }


        $logger->debug($request->getRequestUri());
        $logger->debug($request->getUri());
        $logger->debug($request->getUriForPath('data/' . $pid));

        return new Response('', Response::HTTP_CREATED, ['Location' => $request->getRequestUri() . '/' . $pid]);
    }

    /**
     * Delete a data.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     statusCodes = {
     *         204 = "Returned when successful",
     *         404 = "Returned if the resource was not found"
     *     }
     * )
     *
     * @Annotations\View(statusCode="204")
     *
     * @Annotations\Delete("/data/{id}", requirements={"id" = "[a-zA-Z0-9-]+"})
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @param integer $id ID of entry to delete
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function deleteDataAction(ParamFetcherInterface $paramFetcher, Request $request, $id)
    {
        $oauthUtils = $this->get('vkc.datahub.oauth.oauth');
        $dataManager = $this->get('vkc.datahub.resource.data')->setOwnerId($oauthUtils->getClient()->getId());

        $id_path = 'data.administrativeMetadata.recordWrap.recordID._';

        $entity = $dataManager->getData($id_path, $id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }


        $result = $dataManager->deleteData($id_path, $id);

        if (!$result) {
            throw new HttpException('The record could not be deleted.');
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
