<?php
namespace DataHub\ResourceAPIBundle\Decoder;

use FOS\RestBundle\Decoder\DecoderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use Datahub\ResourceBundle\Service\Builder\ConverterFactoryInterface;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;

/**
 * Decodes XML data.
 *
 * @author Matthias Vandermaesen <matthias.vandermaesen@vlaamsekunstcollectie.be>
 * @package DataHub\ResourceAPIBundle
 */
class XmlDecoder implements DecoderInterface
{
    /**
     * @var Monolog\Logger
     */
    private $logger;

    /**
     * @var DataConverterInterface
     */
    private $converter;

    /**
     * Constructor
     *
     * @param Monolog\Logger $logger
     * @param ConverterFactoryInterface $converterFactory
     */
    public function __construct($logger, ConverterFactoryInterface $converterFactory)
    {
        $this->logger = $logger;
        $this->converter = $converterFactory->getConverter();

        $this->logger->debug('Initialized XMLDecoder');
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        try {
            $result = $this->converter->read($data);

            $this->convertClarkToCatmandu($result);

            var_dump($result);
            die();

            return $result;
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid XML: ' . $e->getMessage());
        }
    }

    /**
     * Convert Clark Notation to Catmandu Notation.
     *
     * This function will recursively traverse through a $result array which is
     * structured in Clark Notation and convert it to 'Catmandu' notation.
     *
     * @todo
     *   This is legacy code. Needs to be moved to a proper class and injected
     *   via the container based on an configuration option. Or we could use an
     *   event listener.
     */
    public function convertClarkToCatmandu(&$result) {
        foreach ($result as $key => &$item) {
            if (isset($item['name'])) {
                // Re-arrange the 'value' key/pair of the current node
                if (is_array($item['value']) && !is_null($item['value'])) {
                    $this->convertClarkToCatmandu($item['value']);
                    $contents = $item['value'];
                } else {
                    $contents = ['_' => $item['value'] ];
                }

                // Remove namespace from attributes names
                $item['attributes'] = array_flip(
                    array_map(function($value) {
                        return preg_replace('/{.*}(.*)/', '$1', $value);
                    }, array_flip($item['attributes']))
                );
                $contents = array_merge($contents, $item['attributes']);

                // Remove namespace from the element name
                $item['name'] = preg_replace('/{.*}(.*)/', '$1', $item['name']);

                // Put it all back together, use the element name as the key for
                // the current node
                $item = [ $item['name'] => $contents ];
            }
        }
    }
}





