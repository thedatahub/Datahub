<?php
namespace DataHub\ResourceAPIBundle\Decoder;

use FOS\RestBundle\Decoder\DecoderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use DataHub\ResourceBundle\Service\DataConvertersService;
use DataHub\ResourceBundle\Data\Converter\DataConverterInterface;

/**
 * Decodes LIDOXML data.
 *
 * @author Tom Van den Abbeele <tom.vda@inuits.eu>
 * @package DataHub\ResourceAPIBundle
 */
class LidoXmlDecoder implements DecoderInterface
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
     * @param DataConvertersService $converters
     */
    public function __construct($logger, $converters)
    {
        $this->setLogger($logger);
        $this->setDataConverters($converters);

        $this->logger->debug('Initialized LidoXmlDecoder');
    }

    /**
     * Set logger service.
     *
     * @param  Monolog\Logger $logger
     * @return LidoXmlDecoder
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Set dataConverters service.
     *
     * @param DataConvertersService $dataConverters
     * @return LidoXmlDecoder
     */
    public function setDataConverters(DataConvertersService $dataConverters)
    {
        $this->converter = $dataConverters->getConverter('lidoxml');

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        $this->logger->debug('Decode LidoXml encoded data');

        try {
            return $this->converter->toArray($data);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException('Invalid LIDOXML: ' . $e->getMessage());
        }
    }
}
