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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use DataHub\ResourceAPIBundle\Form\Type\DataFormType;

/**
 * REST controller for Data.
 *
 * @author  Kalman Olah <kalman@inuits.eu>
 * @package DataHub\ResourceAPIBundle
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
        $logger = $this->get('logger');
        $logger->info('cGET data');

        // get parameters
        $offset = intval($paramFetcher->get('offset'));
        $limit = intval($paramFetcher->get('limit'));

        // prepare data manager
        $oauthUtils = $this->get('datahub.oauth.oauth');
        $dataManager = $this->get('datahub.resource.data');

        try {
            if ($oauthUtils->getClient() !== null)
                $dataManager->setOwnerId($oauthUtils->getClient()->getId());
        } catch (\Exception $e) {
            // TODO:
            // pass
        }

        // get data
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
     * @Annotations\Get("/data/{id}", requirements={"id" = ".+?"})
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

        // prepare data manager
        $oauthUtils = $this->get('datahub.oauth.oauth');
        $dataManager = $this->get('datahub.resource.data');

        try {
            if ($oauthUtils->getClient() !== null)
                $dataManager->setOwnerId($oauthUtils->getClient()->getId());
        } catch (\Exception $e) {
            // TODO:
            // pass
        }

        // get data
        $data = $dataManager->getData($id);

        if (!$data) {
            throw $this->createNotFoundException();
        }

        return $data;
    }

    /**
     * Create a data.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     input = "DataHub\ResourceAPIBundle\Form\Type\DataFormType",
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
     * @return mixed
     * @throws \Exception
     */
    public function postDataAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        $logger = $this->get('logger');
        $logger->debug('POST data');

        // prepare data manager
        $oauthUtils = $this->get('datahub.oauth.oauth');
        $dataManager = $this->get('datahub.resource.data');
        $clientCode = null;
        try {
            if ($oauthUtils->getClient() !== null) {
                $dataManager->setOwnerId($oauthUtils->getClient()->getId());
                $clientCode = $oauthUtils->getClient()->getClientCode();
            }
        } catch (\Exception $e) {
            // TODO:
            // pass
        }

        // get decoded data
        $data = $request->request->all();

        // if everything is configured correctly there should be a matching converter for the provided content type
        // otherwise an internal error is thrown which is good in this case
        $converter = $this->get('datahub.resource.data_converters')->getConverter($request->getContentType());

        // keep the pid that will be return in the location header
        $pid = null;

        // store each record separately
        foreach($converter->getRecords($data) as $record) {
            // get data and object PID's from the data record
            $data_pids = $converter->getRecordDataPids($record);
            $object_pids = $converter->getRecordObjectPids($record);


            // validate the data pid naming convention
            // TODO: validate the data pid naming convention -> <org>:<id> where <org> should match the code related to the OAuth client
            if ($clientCode !== null) {

            }

            // store the record
            $logger->info('Data pids: ' . print_r($data_pids, true));
            $logger->info('Object pids: ' . print_r($object_pids, true));
            try {
                $result = $dataManager->createData($data_pids, $object_pids, $request->getFormat($request->getContentType()), $record, $request->getContent());
            }
            catch (\Exception $e) {
                // TODO: catch a possible unique constraint violation of the data pids
                throw $e;
            }

            // keep the first data pid of the first record to return in the location header afterwards
            if (!isset($pid)) {
                $pid = $data_pids[0];
            }
        }

        return new Response('', Response::HTTP_CREATED, ['Location' => $request->getPathInfo() . '/' . urlencode($pid)]);
    }

    /**
     * Update a data (replaces the entire resource).
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     input = "DataHub\ResourceAPIBundle\Form\Type\DataFormType",
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
     * @Annotations\Put("/data/{id}", requirements={"id" = ".+?"})
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
        $logger->info('PUT data: ' . $id);

        // prepare data manager
        $oauthUtils = $this->get('datahub.oauth.oauth');
        $dataManager = $this->get('datahub.resource.data');

        try {
            if ($oauthUtils->getClient() !== null)
                $dataManager->setOwnerId($oauthUtils->getClient()->getId());
        } catch (\Exception $e) {
            // TODO:
            // pass
        }

        // get decoded data
        $data = $request->request->all();

        // if everything is configured correctly there should be a matching converter for the provided content type
        $converter = $this->get('datahub.resource.data_converters')->getConverter($request->getContentType());

        // store each record separately
        if (count($converter->getRecords($data)) > 1) {
            throw new BadRequestHttpException('Only update one record per request');
        }

        $record = array_shift($converter->getRecords());

        if (!$dataManager->getData($id)) {
            throw $this->createNotFoundException();
        }

        $result = $dataManager->updateData(
            $id,
            $converter->getRecordDataPids($record),
            $converter->getRecordObjectPids($record),
            $request->getFormat($request->getContentType()),
            $record,
            $request->getContent()
        );

        if (!$result) {
            throw new HttpException('The record could not be updated.');
        }

        $logger->info('Updated data:' . $id);

        return new Response('', Response::HTTP_NO_CONTENT);
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
     * @Annotations\Delete("/data/{id}", requirements={"id" = ".+?"})
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
        $oauthUtils = $this->get('datahub.oauth.oauth');
        $dataManager = $this->get('datahub.resource.data');

        try {
            if ($oauthUtils->getClient() !== null)
                $dataManager->setOwnerId($oauthUtils->getClient()->getId());
        } catch (\Exception $e) {
            // TODO:
            // pass
        }

        $data = $dataManager->getData($id);

        if (!$data) {
            throw $this->createNotFoundException();
        }

        $result = $dataManager->deleteData($id);

        if (!$result) {
            throw new HttpException('Unable to delete the requested data');
        }

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
