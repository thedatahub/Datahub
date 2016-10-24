<?php
namespace VKC\DataHub\ResourceAPIBundle\View;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LidoXMLViewHandler
{

    function __construct(){
        global $kernel;
        $logger = $kernel->getContainer()->get('logger');

        $logger->info("LidoXMLViewHandler");
    }
    /**
     * @param ViewHandler $viewHandler
     * @param View        $view
     * @param Request     $request
     * @param string      $format
     *
     * @return Response
     */
    /**
     * Converts the viewdata to a RSS feed. Modify to suit your datastructure.
     *
     * @return Response
     */
    public function createResponse(ViewHandler $handler, View $view, Request $request)
    {
        global $kernel;
        $logger = $kernel->getContainer()->get('logger');

        $logger->info("LidoXMLViewHandler createResponse");
        #$logger->info("Handler: " . $handler);
        $logger->info("View: " . $view->getRoute());
        $logger->info("Request: " . $request);

        $converter = $kernel->getContainer()->get('vkc.datahub.resource.data_converters')->getConverter('lidoxml');

        $view_data = $view->getData();
        //$logger->info(print_r($view_data, true ));
        $data = "";
        if (array_key_exists("results", $view_data)) {
            foreach ($view_data['results'] as $value) {
                //$logger->info(print_r($value, true ));
                try {
                    $data .= $converter->fromArray($value['data']);
                } catch (\Exception $e) {
                    //pass
                }
            }

        } else {
            try {
                $data = $converter->fromArray($view_data['data']);
            } catch (\Exception $e) {
                //pass
            }
        }

        if($data !== "") {
            $content = $data;
            $code = Response::HTTP_OK;
        } else {
            if ($logger) {
                $logger->error($e);
            }
            $content = sprintf('%s:<br/><pre>%s</pre>', $e->getMessage(), $e->getTraceAsString());
            $code = Response::HTTP_BAD_REQUEST;
        }
        return new Response($content, $code, $view->getHeaders());
    }
}