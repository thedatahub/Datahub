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
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 *
 * @todo This class needs heavy refactoring. There are several things to be done:
 *  - Full Doctrine ODM support to manage the I/O with mongo instead of acustom
 *    service class.
 *  - Use ParamConverters to convert the incoming XML to JSON encoded string and
 *    inject both representations into a simple Document Model.
 *  - Use a proper view handler to switch between JSON and XML variant.
 *  - Add validation for the incoming XML object.
 *  - Wire in OAuth support properly.
 *
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
            $result['data'] = json_decode($result['data'], true);
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

        $data['data'] = json_decode($data['data'], true);
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

        $record = $request->request->all();

        if (empty($record)) {
            $response = Response::HTTP_UNPROCESSABLE_ENTITY;
            $headers = ['Message' => 'No record was provided.'];
            return new Response('', $response, $headers);
        }

        // Fetch the datatype from the converter
        $factory = $this->get('datahub.resource.service.builder.converter.factory');
        $dataType = $factory->getConverter()->getDataType();

        // Get the (p)id's
        $dataPids = $dataType->getRecordId($record);
        $objectPids = $dataType->getObjectId($record);

        // Get the JSON & XML Raw variants of the record
        $variantJSON = json_encode($record);
        $variantXML = $request->getContent();

        // Fetch a dataPid. This will be the ID used in the database for this
        // record.
        // @todo Differentiate between 'preferred' and 'alternate' dataPids
        $dataPid = $dataPids[0];

        // Check whether record already exists
        if ($dataManager->getData($dataPid)) {
            return new Response('', Response::HTTP_CONFLICT, ['Message' => 'Record with this ID already exists.']);
        }

        $result = $dataManager->createData(
            $dataPids,
            $objectPids,
            $request->getFormat($request->getContentType()),
            $variantJSON,
            $variantXML
        );

        if (!$result) {
            $logger->info('Could not store new record:' . $dataPid);
            $response = HTTP_BAD_REQUEST;
            $headers = ['Message' => 'Could not store new record'];
        } else {
            $logger->info('Created record:' . $dataPid);
            $response = Response::HTTP_CREATED;
            $headers = [
                'Location' => $request->getPathInfo() . '/' . urlencode($dataPid)
            ];
        }

        return new Response('', $response, $headers);
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

        // Get a decoded record
        $record = $request->request->all();

        if (empty($record)) {
            $response = Response::HTTP_UNPROCESSABLE_ENTITY;
            $headers = ['Message' => 'No record was provided.'];
            return new Response('', $response, $headers);
        }

        // Fetch the datatype from the converter
        $factory = $this->get('datahub.resource.service.builder.converter.factory');
        $dataType = $factory->getConverter()->getDataType();

        // Get the (p)id's
        $data_pids = $dataType->getRecordId($record);
        $object_pids = $dataType->getObjectId($record);

        // Get the JSON & XML Raw variants of the record
        $variantJSON = json_encode($record);
        $variantXML = $request->getContent();

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
                $variantJSON,
                $variantXML
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
                $variantJSON,
                $variantXML
            );

            if (!$result) {
                $logger->info('Could not store updated data:' . $id);
                $response = Response::HTTP_BAD_REQUEST;
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
