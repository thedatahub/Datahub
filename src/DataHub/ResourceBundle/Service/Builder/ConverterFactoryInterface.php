<?php

namespace DataHub\ResourceBundle\Service\Builder;

interface ConverterFactoryInterface {
    public function getConverter($dataType);
}
