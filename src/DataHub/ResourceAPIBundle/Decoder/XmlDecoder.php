<?php
namespace DataHub\ResourceAPIBundle\Decoder;

use FOS\RestBundle\Decoder\DecoderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Datahub\ResourceBundle\Builder\ConverterFactoryInterface;

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
            return $result;
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid XML: ' . $e->getMessage());
        }
    }
}





