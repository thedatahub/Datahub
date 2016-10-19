<?php

namespace VKC\DataHub\ResourceAPIBundle\Decoder;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

use FOS\RestBundle\Decoder\DecoderInterface;

/**
 * Decodes LIDOXML data.
 *
 * @author Tom Van den Abbeele <tom.vda@inuits.eu>
 * @package VKC\DataHub\ResourceAPIBundle
 */
class LidoXmlDecoder implements DecoderInterface
{
    private $converter;

    public function __construct()
    {
        $dataConverters = $this->get('vkc.datahub.resource.data_converters');
        $this->converter = $dataConverters->getConverter('LIDOXML');

        $output = new ConsoleOutput();
        $output->writeln('Construct LidoXmlDecoder');
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data)
    {
        try {
            return $dataConverter->toArray($data);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException('Invalid LIDOXML: ' . $e->getMessage());
        }
    }
}
