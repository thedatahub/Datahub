<?php

namespace DataHub\ResourceBundle\Converter;

use DataHub\ResourceBundle\DataType\DataTypeInterface;
use Sabre\Xml\Service;
use Sabre\Xml\LibXMLException;
use DOMDocument;

/**
 * Converter service class.
 *
 * This converter wraps the Sabre XML library to (de)serialize objects.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
class SabreXMLConverterService implements ConverterServiceinterface {

    protected $service;

    protected $dataType;

    protected $schemaLocation;

    public function __construct(DataTypeInterface $dataType) {
        $this->dataType = $dataType;
        $this->service = new Service();
        $this->service->namespaceMap = $dataType->getNamespaceMap();
        $this->schemaLocation = __DIR__.'/../Resources/schemas';
    }

    /**
     * Validates XML against a XML schema.
     *
     * Validate against an XML schema set in a concrete implementation of
     * DataHub\ResourceBundle\DataType\DataTypeInterface.
     * This validator uses XML catalogs since external schema's are downloaded
     * on the fly. W3C serves their schemata (xml.xsd) with a delay to discourage
     * abuse. The DataTypeInterface implementation also defines the XML catalog
     * which gets loaded via libxml_set_external_entity_loader().
     *
     * @param string $serializedData The XML string to validate
     *
     * @return bool The result of of the validation
     * @throws RuntimeException If the catalog definition contains invalid references.
     * @throws LibXMLException An exeption bubbling up LibXML errors thrown by validation.
     */
    public function validate($serializedData) {
        // Set the catalog
        $catalog = $this->dataType->getCatalog();
        foreach ($catalog as &$item) {
            $item = sprintf('%s%s', $this->schemaLocation, $item);
        }

        // Set the schema
        $schema = $this->dataType->getSchema();
        $schema = sprintf('%s%s', $this->schemaLocation, $schema);

        // Take over error handling.
        $useInternalErrorsPreviousState = libxml_use_internal_errors(true);

        // Disable the default entity loader, we do it ourselves.
        $disableEntityLoaderPreviousState = libxml_disable_entity_loader(false);

        try {
            // Set the XML catalog at runtime
            if (!empty($catalog)) {
                libxml_set_external_entity_loader(
                    function ($public, $system, $context) use ($catalog) {
                        if (is_file($system)) {
                            return $system;
                        }
                        if (isset($catalog[$system])) {
                            return $catalog[$system];
                        }

                        $message = sprintf(
                            "Failed to load external entity: System: %s; Context: %s",
                            var_export($system, 1),
                            strtr(var_export($context, 1), [" (\n  " => '(', "\n " => '', "\n" => ''])
                        );
                        throw new \RuntimeException($message);
                    }
                );
            }

            // Validation via \DOMDocument
            $dom = new DOMDocument();
            $dom->loadXML($serializedData);
            $result = $dom->schemaValidate($schema);
            if (!$result) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                throw new LibXMLException($errors);
            }
        } finally {
            // Restore libxml_* to previous state
            // @see https://github.com/simplesamlphp/simplesamlphp/issues/241
            register_shutdown_function('libxml_use_internal_errors', $useInternalErrorsPreviousState);
            register_shutdown_function('libxml_disable_entity_loader', $disableEntityLoaderPreviousState);
        }

        return $result;
    }

    public function read($serializedData) {
      return $this->service->parse($serializedData);
    }

    public function write($object) {
        $rootElement = $this->dataType->getRootElement();
        return $this->service->write($rootElement, $object);
    }

    public function getDataType() {
        return $this->dataType;
    }
}
