<?php

namespace DataHub\ResourceBundle\Service\DataType;

class DataTypeLido implements DataTypeInterface {
    protected $namespaceMap;

    protected $rootElement;

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
    }

    public function getNamespaceMap() {
        return $this->namespaceMap;
    }

    public function getRootElement() {
        return $this->rootElement;
    }
}
