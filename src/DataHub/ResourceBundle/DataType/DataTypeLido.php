<?php

namespace DataHub\ResourceBundle\DataType;

/**
 * DataType LIDO class
 *
 * A concrete implementation of the LIDO XML Datatype.
 * @see http://lido-schema.org
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
class DataTypeLido implements DataTypeInterface {

    protected $namespaceMap;

    protected $rootElement;

    /**
     * {@inheritdoc}
     */
    public function __construct() {
         $this->namespaceMap = [
            'http://www.w3.org/XML/1998/namespace' => 'xml',
            'http://www.w3.org/2001/XMLSchema' => 'xsd',
            'http://www.lido-schema.org' => 'lido',
            'http://www.opengis.net/gml' => 'gml',
            'http://www.mda.org.uk/spectrumXML/Documentation' => 'doc',
            'http://www.w3.org/2001/XMLSchema-instance' => 'xsi'
        ];

        $this->rootElement = '{http://www.lido-schema.org}lido';

        $this->catalog = [
            'http://schemas.opengis.net/gml/3.1.1/smil/smil20.xsd' => '/smil/2.0/smil20.xsd',
            'http://www.w3.org/1999/xlink.xsd' => '/xlink/1999/xlink.xsd',
            'http://www.w3.org/2001/xml.xsd' => '/xml/2001/xml.xsd',
            'http://schemas.opengis.net/gml/3.1.1/base/feature.xsd' => '/opengis/3.1.1/base/gml.xsd',
        ];

        $this->schema = '/lido/1.0/lido-v1.0.xsd';
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalog() {
        return $this->catalog;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema() {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespaceMap() {
        return $this->namespaceMap;
    }

    /**
     * {@inheritdoc}
     */
    public function getRootElement() {
        return $this->rootElement;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectId($record) {
        $result = [];
        $iterator = new \ArrayIterator($record);

        $ids = new \CallbackFilterIterator($iterator, function($current, $key, $iterator) {
            if ($current['name'] == '{http://www.lido-schema.org}objectPublishedID') {
                return TRUE;
            }
            return FALSE;
        });

        foreach (new \IteratorIterator($ids) as $id) {
            array_push($result, $id['value']);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordId($record) {
        $result = [];
        $iterator = new \ArrayIterator($record);

        $ids = new \CallbackFilterIterator($iterator, function($current, $key, $iterator) {
            if ($current['name'] == '{http://www.lido-schema.org}lidoRecID') {
                return TRUE;
            }
            return FALSE;
        });

        foreach (new \IteratorIterator($ids) as $id) {
            array_push($result, $id['value']);
        }

        return $result;
    }
}
