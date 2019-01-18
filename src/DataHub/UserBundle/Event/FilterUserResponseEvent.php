<?php

namespace DataHub\UserBundle\Event;

use DataHub\UserBundle\Document\UserInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterUserResponseEvent extends Event
{
    /**
     * @var null|UserInterface
     */
    protected $user;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    public function __construct(UserInterface $user, Request $request, Response $response)
    {
        $this->user = $user;
        $this->request = $request;
        $this->response = $response;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set a new Response object.
     * 
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }
}