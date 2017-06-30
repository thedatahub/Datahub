<?php

namespace DataHub\ResourceAPIBundle\Controller;

use DataHub\ResourceAPIBundle\Document\Record;
use DataHub\ResourceAPIBundle\Repository\RecordRepository;
use FOS\RestBundle\Controller\Annotations as FOS;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Hateoas\HateoasBuilder;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\OffsetRepresentation;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * REST controller for Records.
 *
 * @author  Kalman Olah <kalman@inuits.eu>
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 *
 * @todo This class needs heavy refactoring. There are several things to be done:
 *  - Use ParamConverters to convert the incoming XML to JSON encoded string and
 *    inject both representations into a simple Document Model.
 *  - Implement proper content negotation.
 *  - Implement event listeners to offload conversion JSON/XML and fetching of ids.
 *
 * @package DataHub\ResourceAPIBundle
 */
class RecordController extends Controller
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
     * @FOS\Get("/data")
     *
     * @FOS\QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing entries.")
     * @FOS\QueryParam(name="limit", requirements="\d{1,2}", default="5", description="How many entries to return.")
     * @FOS\QueryParam(name="sort", requirements="[a-zA-Z\.]+,(asc|desc|ASC|DESC)", nullable=true, description="Sorting field and direction.")
     *
     * @FOS\View(
     *     serializerGroups={"list"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     *
     * @return array<mixed>
     */
    public function cgetRecordsAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        // get parameters
        $offset = intval($paramFetcher->get('offset'));
        $limit = intval($paramFetcher->get('limit'));
        // @todo
        //   Remove sorting, not relevant here

        $recordRepository = $this->get('datahub.resource_api.repository.default');
        $records = $recordRepository->findBy(array(), null, $limit, $offset);
        $total = $recordRepository->count();

        $offsetCollection = new OffsetRepresentation(
            new CollectionRepresentation(
                $records, 'records', 'records'
            ),
            'get_records',
            array(),
            $offset,
            $limit,
            $total
        );

        $context = SerializationContext::create()->setGroups(array('Default','json'));
        $json = $this->get('serializer')->serialize($offsetCollection, 'json', $context);

        return new Response($json, Response::HTTP_OK, array('Content-Type' => 'application/hal+json'));
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
     * @ParamConverter(class="DataHub\ResourceAPIBundle\Document\Record", converter="record_converter")
     * @FOS\Get("/data/{recordIds}", requirements={"recordIds" = ".+?"})
     * @FOS\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @param Request $request the request object
     * @param string $id the internal id of the record
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function getRecordAction(Request $request, Record $record)
    {
        // Circumventing the XML serializer here, since we already have the
        // raw XML input from the store.
        if ($request->getRequestFormat() == 'xml') {
            return new Response($record->getRaw(), Response::HTTP_OK, array('Content-Type' => 'application/xml'));
        }

        $hateoas = HateoasBuilder::create()->build();
        $context = SerializationContext::create()->setGroups(array('json'));
        $json = $hateoas->serialize($record, 'json', $context);

        return new Response($json, Response::HTTP_OK, array('Content-Type' => 'application/hal+json'));
    }

    /**
     * Create a record.
     *
     * @ApiDoc(
     *     section = "DataHub",
     *     resource = true,
     *     statusCodes = {
     *         201 = "Returned when successful",
     *         400 = "Returned if the form could not be validated, or record already exists",
     *     }
     * )
     *
     * @FOS\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true,
     *     statusCode=201
     * )
     *
     * @FOS\Post("/data")
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @return mixed
     * @throws \Exception
     */
    public function postRecordAction(ParamFetcherInterface $paramFetcher, Request $request)
    {
        $data = $request->request->all();

        if (empty($data)) {
            throw new UnprocessableEntityHttpException('No record was provided.');
        }

        // Fetch the datatype from the converter
        $factory = $this->get('datahub.resource.builder.converter.factory');
        $dataType = $factory->getConverter()->getDataType();

        // Get the (p)id's
        $dataPids = $dataType->getRecordId($data);
        $objectPids = $dataType->getObjectId($data);

        // Get the JSON & XML Raw variants of the record
        $variantJSON = json_encode($data);
        $variantXML = $request->getContent();

        // Fetch a dataPid. This will be the ID used in the database for this
        // record.
        // @todo Differentiate between 'preferred' and 'alternate' dataPids
        $dataPid = $dataPids[0];

        // Check whether record already exists
        $recordRepository = $this->get('datahub.resource_api.repository.default');
        $record = $recordRepository->findOneByProperty('recordIds', $dataPid);
        if ($record instanceof Record) {
            throw new ConflictHttpException('Record with this ID already exists.');
        }

        $documentManager = $this->get('doctrine_mongodb')->getManager();

        $record = new Record();
        $record->setRecordIds($dataPids);
        $record->setObjectIds($objectPids);
        $record->setRaw($variantXML);
        $record->setJson($variantJSON);

        $documentManager->persist($record);
        $documentManager->flush();

        $id = $record->getId();

        if (!$id) {
            throw new BadRequestHttpException('Could not store new record.');
        } else {
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
     * @FOS\View(
     *     serializerGroups={"single"},
     *     serializerEnableMaxDepthChecks=true
     * )
     *
     * @FOS\Put("/data/{id}", requirements={"id" = ".+?"})
     *
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @param Request $request the request object
     * @param integer $id ID of entry to update
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function putRecordAction(Request $request, $id)
    {
        // Get a decoded record
        $record = $request->request->all();

        if (empty($record)) {
            throw new UnprocessableEntityHttpException('No record was provided.');
        }

        // Fetch the datatype from the converter
        $factory = $this->get('datahub.resource.builder.converter.factory');
        $dataType = $factory->getConverter()->getDataType();

        // Get the (p)id's
        $recordIds = $dataType->getRecordId($record);
        $objectIds = $dataType->getObjectId($record);

        // Get the JSON & XML Raw variants of the record
        $variantJSON = json_encode($record);
        $variantXML = $request->getContent();

        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $recordRepository = $this->get('datahub.resource_api.repository.default');
        $record = $recordRepository->findOneByProperty('recordIds', $id);

        // If the record does not exist, create it, if it does exist, update the existing record.
        // The ID of a particular resource is not generated server side, but determined client side.
        // The ID is the resource URI to which the PUT request was made.
        //
        //   See: https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.6
        //
        if (!$record instanceof Record) {
            $record = new Record();
            $record->setRecordIds($recordIds);
            $record->setObjectIds($objectIds);
            $record->setRaw($variantXML);
            $record->setJson($variantJSON);

            $documentManager->persist($record);
            $documentManager->flush();

            $id = $record->getId();

            if (!$id) {
                throw new BadRequestHttpException('Could not store new record.');
            } else {
                $response = Response::HTTP_CREATED;
                $headers = [];
            }
        } else {
            $record->setRecordIds($recordIds);
            $record->setObjectIds($objectIds);
            $record->setRaw($variantXML);
            $record->setJson($variantJSON);

            $documentManager->flush();

            $id = $record->getId();

            if (!$id) {
                throw new BadRequestHttpException('Could not store updated record.');
            } else {
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
     * @FOS\View(statusCode="204")
     * @FOS\Delete("/data/{recordIds}", requirements={"recordIds" = ".+?"})
     * @ParamConverter(class="DataHub\ResourceAPIBundle\Document\Record", converter="record_converter")
     *
     * @param Request $request the request object
     * @param integer $id ID of entry to delete
     *
     * @return mixed
     *
     * @throws NotFoundHttpException if the resource was not found
     */
    public function deleteRecordAction(Request $request, Record $record)
    {
        $documentManager = $this->get('doctrine_mongodb')->getManager();
        $documentManager->remove($record);
        $documentManager->flush();
    }
}
