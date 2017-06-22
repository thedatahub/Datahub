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


/**
 * Implements the various verbs the OAI endpoint stream_supports.
 */
class Repository implements InterfaceRepository
{
    private $dataService;
    private $dataConvertersService;
    private $dataConverter;

    private $oaiBaseUrl;
    private $repositoryName;
    private $contactEmail;

    private $paginationSize;

    /**
     * Constructor.
     *
     * @param DataService           $dataService
     * @param DataConvertersService $dataConvertersService
     */
    public function __construct(DataService $dataService) {
        $this->dataService = $dataService;
    }

    /**
     * Implements verb Identify.
     *
     * @return Identity
     */
    public function identify()
    {
        return new ImplementationIdentity(
            $this->getRepositoryName(),
            $this->getEarliestDateStamp(),
            $this->getKeepDeletedRecords(),
            $this->getContactEmail(),
            $this->getGranularity()
        );
    }

    /**
     * Implements verb ListSets.
     *
     * @return InterfaceSetList
     */
    public function listSets()
    {
        $items = [];
        $items[] = new Set('set:all', 'All records');
        return new SetList($items);
    }

    /**
     * Implements verb ListSetsByToken.
     *
     * @param string $token
     * @return InterfaceSetList
     */
    public function listSetsByToken($token)
    {
        $params = $this->decodeResumptionToken($token);
        return $this->listSets();
    }

    /**
     * Implements verb getRecord.
     *
     * @param string $metadataFormat
     * @param string $identifier
     *
     * @return Record
     */
    public function getRecord($metadataFormat, $identifier)
    {
        // Fetch record
        $record = $this->dataService->getData($identifier);

        // Throw exception if record does not exist
        if (!$record) {
            throw new IdDoesNotExistException('No matching identifier ' . $identifier);
        }

        $data = $record['raw'];

        $recordMetadata = new \DOMDocument();
        $recordMetadata->loadXML($data);
        return new Record(new Header($identifier, new \DateTime()), $recordMetadata);
    }

    /**
     * Implements verb ListRecords.
     *
     * @param string $metadataFormat metadata format of the records to be fetched or null if only headers are fetched
     * @param DateTime $from start record date/time
     * @param DateTime $until end record date/time
     * @param string $set name of the set containing this record
     *
     * @return RecordList
     */
    public function listRecords($metadataFormat = null, DateTime $from = null, DateTime $until = null, $set = null, $offset = 0)
    {
        $items = array();
        $token = '';
        $data = $this->dataService->cgetData($offset, $offset + $this->getPaginationSize());
        $completeListSize = $data['count'];

        if ($completeListSize > $offset+$this->getPaginationSize()) {
            // Include token only if more records exist than shown
            $token = $this->encodeResumptionToken($offset + $this->getPaginationSize(), $from, $until, $metadataFormat, $set);
        }

        foreach ($data['results'] as $record) {
            // speed up OAI calls by not serializing and using raw data
            $data = $record['raw'];

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
     * Implements verb ListRecordsByToken.
     *
     * @param string $token
     *
     * @return RecordList
     */
    public function listRecordsByToken($token)
    {
        $params = $this->decodeResumptionToken($token);

        $items = $this->listRecords($params['metadataPrefix'], $params['from'], $params['until'], $params['set'], $params['offset']);

        // Only show if there are more records available, else $token = '';
        $token = '';
        if ($items->getCompleteListSize() > $params['offset']+$this->getPaginationSize()) { // should check on total size!
            $token = $this->encodeResumptionToken(
                $params['offset'] + $this->getPaginationSize(),
                $params['from'],
                $params['until'],
                $params['metadataPrefix'],
                $params['set']
            );
        }

        return new OaiRecordList($items->getItems(), $token, $items->getCompleteListSize());
    }

    /**
     * Implements verb ListMetadataFormats.
     *
     * @param string $identifier
     *
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
     * Decodes resumption token.
     *
     * Possible properties are:
     *
     * ->offset
     * ->metadataPrefix
     * ->set
     * ->from (timestamp)
     * ->until (timestamp)
     *
     * @param string $token
     *
     * @return array containing resumption token parameters
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
     * Encodes a resumption token.
     *
     * @param int $offset
     * @param DateTime $from
     * @param DateTime $util
     * @param string $metadataPrefix
     * @param string $set
     *
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
     * Returns OAI endpoint base URL.
     * @return string the base URL of the OAI repository
     */
    public function getBaseUrl()
    {
        return $this->oaiBaseUrl;
    }

    /**
     * Sets OAI endpoint base URL.
     * @param string $oaiBaseUrl the base URL of the OAI repository
     */
    public function setBaseUrl($oaiBaseUrl)
    {
        $this->oaiBaseUrl = $oaiBaseUrl;
    }

    /**
     * Returns OAI repository name.
     * @return string the base URL of the OAI repository
     */
    public function getRepositoryName()
    {
        return $this->repositoryName;
    }

    /**
     * Sets OAI repository name.
     * @param string $oaiBaseUrl the name of the OAI repository
     */
    public function setRepositoryName($repoName)
    {
        $this->repositoryName = $repoName;
    }

    /**
     * Returns OAI endpoint base URL.
     * @return string the base URL of the OAI repository
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }

    /**
     * Sets OAI endpoint base URL.
     * @param string $email string with single email address or string of email addresses separated by commas
     */
    public function setContactEmail($email)
    {
        $this->contactEmail = explode(',', $email);
    }

    /**
     * Returns number of records for pages in OAI endpoint.
     *
     * @return int
     */
    public function getPaginationSize()
    {
        return $this->paginationSize;
    }

    /**
     * Sets number of records for pages in OAI endpoint.
     * @param int $pageSize size of pages
     */
    public function setPaginationSize($pageSize)
    {
        $this->paginationSize = $pageSize;
    }

    /**
     * Get whether deleted records are kept.
     *
     * @return string yes or no
     */
    public function getKeepDeletedRecords()
    {
        return 'no';
    }

    /**
     * Get earliest modified timestamp.
     *
     * @return DateTime DateTime object
     */
    public function getEarliestDateStamp()
    {
        return new DateTime();
    }

    /**
     * Returns datetime granularity for the OAI repository
     * @return string datetime granularity for the OAI repository
     */
    public function getGranularity()
    {
        return "YYYY-MM-DDThh:mm:ssZ";
    }
}
