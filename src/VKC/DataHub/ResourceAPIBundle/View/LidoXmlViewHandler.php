<?php
namespace VKC\DataHub\ResourceAPIBundle\View;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use VKC\DataHub\ResourceBundle\Service\DataConvertersService;
use VKC\DataHub\ResourceBundle\Data\Converter\DataConverterInterface;

/**
 * Serializes LIDOXML data.
 *
 * @author Pavel Grochal <pavel.grochal@inuits.eu>
 * @package VKC\DataHub\ResourceAPIBundle
 */
class LidoXmlViewHandler
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

        $this->logger->debug('Initialized LidoXmlViewHandler');
    }

    /**
     * Set logger service.
     *
     * @param  Monolog\Logger $logger
     * @return LidoXmlViewHandler
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
     * @return LidoXmlViewHandler
     */
    public function setDataConverters(DataConvertersService $dataConverters)
    {
        $this->converter = $dataConverters->getConverter('lidoxml');

        return $this;
    }

    /**
     * Converts the viewdata to LIDOXML
     *
     * @param ViewHandler $viewHandler
     * @param View        $view
     * @param Request     $request
     * @param string      $format
     *
     * @return Response
     */
    public function createResponse(ViewHandler $handler, View $view, Request $request)
    {
        $this->logger->debug('LidoXmlViewHandler createResponse');
        #$this->logger->info("Handler: " . $handler);
        $this->logger->debug('View: ' . $view->getRoute());
        $this->logger->debug('Request: ' . $request);

        $data = '';
        $view_data = $view->getData();

        if (array_key_exists('results', $view_data)) {
            // serialize multiple results
            try {
                $data = $this->converter->fromArray(array_column($view_data['results'], 'data'));
            } catch (\Exception $e) {
                //pass
            }

        } else {
            // serialize a single result
            try {
                $data = $this->converter->fromArray($view_data['data']);
            } catch (\Exception $e) {
                //pass
            }
        }

        if($data !== '') {
            return new Response($data, Response::HTTP_OK, $view->getHeaders());
        } else {
            // TODO: search for a better error response
            throw new \Exception('Impossible to response convert to LIDOXML');
        }
    }
}
