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
     * List records.
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

        foreach($data['results'] as &$result) {
            unset($result['raw']);
        }

        return $data;
    }

    /**
     * Get a single record.
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

        unset($data['raw']);

        if (!$data) {
            throw $this->createNotFoundException();
        }

        return $data;
    }

    /**
     * Create a record.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     input = "DataHub\ResourceAPIBundle\Form\Type\DataFormType",
     *     statusCodes = {
     *         201 = "Returned when successful",
     *         400 = "Returned if the form could not be validated, or record already exists",
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

        // Throw exception 500 if data could not be parsed and is empty
        if (empty($converter->getRecords($data))) {
            return new Response('', Response::HTTP_BAD_REQUEST, ['Message' => 'Could not parse data.']);
            throw new \Exception('Could not parse data.');
        }

        // keep the pid that will be return in the location header
        $pid = null;

        // store each record separately
        foreach($converter->getRecords($data) as $record) {
            // get data and object PID's from the data record
            $data_pids = $converter->getRecordDataPids($record);
            $object_pids = $converter->getRecordObjectPids($record);

            // Check whether record already exists
            if ($dataManager->getData($data_pids[0])) {
                return new Response('', Response::HTTP_BAD_REQUEST, ['Message' => 'Record with this ID already exists.']);
            }


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
                return new Response('', Response::HTTP_BAD_REQUEST);
            }

            // keep the first data pid of the first record to return in the location header afterwards
            if (!isset($pid)) {
                $pid = $data_pids[0];
            }
        }

        return new Response('', Response::HTTP_CREATED, ['Location' => $request->getPathInfo() . '/' . urlencode($pid)]);
    }

    /**
     * Update a record (replaces the entire resource).
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     input = "DataHub\ResourceAPIBundle\Form\Type\DataFormType",
     *     statusCodes = {
     *         201 = "Returned when a record was succesfully created",
     *         204 = "Returned when an existing recurd was succesfully updated",
     *         400 = "Returned if the record could not be stored or parsed",
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

        // if everything is configured correctly there should be a matching converter for the provided content type
        $converter = $this->get('datahub.resource.data_converters')->getConverter($request->getContentType());

        // Get a decoded record
        $data = $request->request->all();
        $records = $converter->getRecords($data);
        if (empty($records)) {
            return new Response('', Response::HTTP_BAD_REQUEST, ['Message' => 'Could not parse data.']);
        }
        $record = array_shift($records);

        $data_pids = $converter->getRecordDataPids($record);
        $object_pids = $converter->getRecordObjectPids($record);

        // If the record does not exist, create it, if it does exist, update the existing record.
        // The ID of a particular resource is not generated server side, but determined client side.
        // The ID is the resource URI to which the PUT request was made.
        //
        //   See: https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.6
        //
        if (!$dataManager->getData($id)) {
            $result = $dataManager->createData(
                $data_pids,
                $object_pids,
                $request->getFormat($request->getContentType()),
                $record,
                $request->getContent()
            );

            if (!$result) {
                $logger->info('Could not store new record:' . $id);
                $response = HTTP_BAD_REQUEST;
                $headers = ['Message' => 'Could not store new record'];
            } else {
                $logger->info('Created record:' . $id);
                $response = Response::HTTP_CREATED;
                $headers = [];
            }
        } else {
            $result = $dataManager->updateData(
                $id,
                $data_pids,
                $object_pids,
                $request->getFormat($request->getContentType()),
                $record,
                $request->getContent()
            );

            if (!$result) {
                $logger->info('Could not store updated data:' . $id);
                $response = HTTP_BAD_REQUEST;
                $headers = ['Message' => 'Could not store updated data'];
            } else {
                $logger->info('Updated record:' . $id);
                $response = Response::HTTP_NO_CONTENT;
                $headers = [];
            }
        }

        return new Response('', $response, $headers);
    }

    /**
     * Delete a record.
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
