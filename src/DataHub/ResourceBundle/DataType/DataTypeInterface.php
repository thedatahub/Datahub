<?php

namespace DataHub\ResourceBundle\DataType;

/**
 * DataType interface
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceBundle
 */
interface DataTypeInterface {
    /**
     * Get a map of XML namespaces which define the dataType.
     */
    public function getNamespaceMap();

    /**
     * Get the XML root element.
     */
    public function getRootElement();

    /**
     * Get the catalog map with local XSD schema's for the dataType.
     *
     * An XML catalog is a structure used to map global URL's where XSD schema's
     * reside to their local counter parts stored on the filesystem. This technique
     * is used to minimize the overhead of fetching the XSD schema over HTTP on
     * each validation pass.
     */
    public function getCatalog();

    /**
     * Get the XSD schema.
     *
     * This is the locally stored XSD schema for this dataType which wil be used
     * for the actual validation of the incoming object.
     */
    public function getSchema();

    /**
     * Get the Object ID.
     *
     * A record describes a (set of) objects. This method extracts the ID's assigned
     * to those obejcts. These could be different from the ID's which dereference the
     * records. An 'object' should be seen as either a virtual (digital) or physical object.
     */
    public function getObjectId($record);

    /**
     * Get the Record ID.
     *
     * This is the ID of the record proper. A record could be dereferenced by multiple
     * ID's. This method should fetch all relevant record ID's for a dataType.
     */
    public function getRecordId($record);
}
