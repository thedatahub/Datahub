<?php

namespace DataHub\OAIBundle\Repository;

use DateTime;
use OpenSkos2\OaiPmh\Concept as OaiConcept;
use Picturae\OaiPmh\Exception\IdDoesNotExistException;
use Picturae\OaiPmh\Implementation\MetadataFormatType as ImplementationMetadataFormatType;
use Picturae\OaiPmh\Implementation\RecordList as OaiRecordList;
use Picturae\OaiPmh\Implementation\Repository\Identity as ImplementationIdentity;
use Picturae\OaiPmh\Implementation\Set;
use Picturae\OaiPmh\Implementation\SetList;
use Picturae\OaiPmh\Implementation\Record;
use Picturae\OaiPmh\Implementation\Record\Header;
use Picturae\OaiPmh\Interfaces\MetadataFormatType;
use Picturae\OaiPmh\Interfaces\RecordList;
use Picturae\OaiPmh\Interfaces\Repository as InterfaceRepository;
use Picturae\OaiPmh\Interfaces\Repository\Identity;
use Picturae\OaiPmh\Interfaces\SetList as InterfaceSetList;

use DataHub\ResourceBundle\Service\DataService;
use DataHub\ResourceBundle\Service\DataConvertersService;


class Repository implements InterfaceRepository
{
    private $dataService;

    private $dataConvertersService;

    private $dataConverter;

    const PAGINATION_OFFSET = 5;

    public function __construct(DataService $dataService, DataConvertersService $dataConvertersService) {
        $this->dataService = $dataService;
        $this->dataConvertersService = $dataConvertersService;

        $this->dataConverter = $this->dataConvertersService->getConverter('lidoxml');
    }

    /**
     * @return string the base URL of the repository
     */
    public function getBaseUrl()
    {
        return 'http://datahub_ubuntu:8000/oai';
    }

    /**
     * @return Identity
     */
    public function identify()
    {
        // $repositoryName, earliestDatestamp, $deletedRecord, $adminEmails, $granularity
        return new ImplementationIdentity('Datahub', (new \DateTime()), 'no', ['nassia@inuits.eu'], 'YYYY-MM-DDThh:mm:ssZ');
    }

    /**
     * @return InterfaceSetList
     */
    public function listSets()
    {
        $items = [];
        $items[] = new Set('set:all', 'All records');
        return new SetList($items);
    }

    /**
     * @param string $token
     * @return InterfaceSetList
     */
    public function listSetsByToken($token)
    {
        $params = $this->decodeResumptionToken($token);
        return $this->listSets();
    }

    /**
     * @param string $metadataFormat
     * @param string $identifier
     * @return Record
     */
    public function getRecord($metadataFormat, $identifier)
    {
        // Fetch record
        // $record = $this->getSomeRecord($identifier);

        $record = $this->dataService->getData($identifier);

        // Throw exception if it does not exists
        if (!$record) {
            throw new IdDoesNotExistException('No matching identifier ' . $identifier);
        }

        // serialize a single result
        try {
            $data = $this->dataConverter->fromArray($record['data']);
        } catch (\Exception $e) {
            //pass
        }


        $recordMetadata = new \DOMDocument();
        $recordMetadata->loadXML($data);
        return new Record(new Header($identifier, new \DateTime()), $recordMetadata);
    }

    /**
     * @param string $metadataFormat metadata format of the records to be fetch or null if only headers are fetched
     * (listIdentifiers)
     * @param DateTime $from
     * @param DateTime $until
     * @param string $set name of the set containing this record
     * @return RecordList
     */
    public function listRecords($metadataFormat = null, DateTime $from = null, DateTime $until = null, $set = null, $offset = 0)
    {
        $items = array();
        $token = '';
        $data = $this->dataService->cgetData($offset, $offset + Repository::PAGINATION_OFFSET);
        $completeListSize = $data['count'];
        //dump($data['count']);dump($offset+Repository::PAGINATION_OFFSET);die;



        if ($completeListSize > $offset+Repository::PAGINATION_OFFSET) {
            // Include token only if more records exist than shown
            $token = $this->encodeResumptionToken($offset + Repository::PAGINATION_OFFSET, $from, $until, $metadataFormat, $set);
        }

        foreach ($data['results'] as $record) {
            // serialize a single result
            try {
                $data = $this->dataConverter->fromArray($record['data']);
            } catch (\Exception $e) {
                //pass
            }

            $recordMetadata = new \DOMDocument();
            $recordMetadata->loadXML($data);
            $xpath = new \DOMXpath($recordMetadata);
            $lidoRecID = $xpath->query("/lido:lido/lido:lidoRecID");
            $identifier = $lidoRecID->item(0)->nodeValue;

            $items[] = new Record(new Header($identifier, new \DateTime()), $recordMetadata);
        }



        return new OaiRecordList($items, $token, $completeListSize);
    }

    /**
     * @param string $token
     * @return RecordList
     */
    public function listRecordsByToken($token)
    {
        $params = $this->decodeResumptionToken($token);


        $items = $this->listRecords($params['metadataPrefix'], $params['from'], $params['until'], $params['set'], $params['offset']);

        // Only show if there are more records available else $token = '';
        $token = '';
        if ($items->getCompleteListSize() > $params['offset']+Repository::PAGINATION_OFFSET) { // should check on total size!
            $token = $this->encodeResumptionToken(
                $params['offset'] + Repository::PAGINATION_OFFSET,
                $params['from'],
                $params['until'],
                $params['metadataPrefix'],
                $params['set']
            );
        }

        return new OaiRecordList($items->getItems(), $token, $items->getCompleteListSize());
    }

    /**
     * @param string $identifier
     * @return MetadataFormatType[]
     */
    public function listMetadataFormats($identifier = null)
    {
        $formats = [];
        $formats[] = new ImplementationMetadataFormatType(
            'oai_dc',
            'http://www.openarchives.org/OAI/2.0/oai_dc.xsd',
            'http://www.openarchives.org/OAI/2.0/oai_dc/'
        );

        $formats[] = new ImplementationMetadataFormatType(
            'oai_rdf',
            'http://www.openarchives.org/OAI/2.0/rdf.xsd',
            'http://www.w3.org/2004/02/skos/core#'
        );

        $formats[] = new ImplementationMetadataFormatType(
            'oai_lido',
            'http://www.lido-schema.org/schema/v1.0/lido-v1.0.xsd',
            'http://www.lido-schema.org/'
        );

        return $formats;
    }

    /**
     * Decode resumption token
     * possible properties are:
     *
     * ->offset
     * ->metadataPrefix
     * ->set
     * ->from (timestamp)
     * ->until (timestamp)
     *
     * @param string $token
     * @return array
     */
    private function decodeResumptionToken($token)
    {
        $params = (array) json_decode(base64_decode($token));

        if (!empty($params['from'])) {
            $params['from'] = new \DateTime('@' . $params['from']);
        }

        if (!empty($params['until'])) {
            $params['until'] = new \DateTime('@' . $params['until']);
        }

        return $params;
    }

    /**
     * Get resumption token
     *
     * @param int $offset
     * @param DateTime $from
     * @param DateTime $util
     * @param string $metadataPrefix
     * @param string $set
     * @return string
     */
    private function encodeResumptionToken(
        $offset = 0,
        DateTime $from = null,
        DateTime $util = null,
        $metadataPrefix = null,
        $set = null
    ) {
        $params = [];
        $params['offset'] = $offset;
        $params['metadataPrefix'] = $metadataPrefix;
        $params['set'] = $set;
        $params['from'] = null;
        $params['until'] = null;

        if ($from) {
            $params['from'] = $from->getTimestamp();
        }

        if ($util) {
            $params['until'] = $util->getTimestamp();
        }

        return base64_encode(json_encode($params));
    }

    /**
     * Get earliest modified timestamp
     *
     * @return DateTime
     */
    public function getEarliestDateStamp()
    {
        // Fetch earliest timestamp
        return new DateTime();
    }

    public function getGranularity() {
   	    return "YYYY-MM-DDThh:mm:ssZ";
    }

    /**
     * Gets pagination size
     *
     * @return int
     */
    private function getPaginationSize()
    {
        // Fetch earliest timestamp
        return $this->PAGINATION_OFFSET;
    }
}
