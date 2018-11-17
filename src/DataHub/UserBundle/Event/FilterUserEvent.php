<?php

namespace DataHub\UserBundle\Event;

use DataHub\UserBundle\Document\UserInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterUserEvent extends Event
{
    /**
     * @var FormInterface
     */
    protected $form;
    
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var null|UserInterface
     */
    protected $user;

    /**
     * FormEvent constructor
     * 
     * @param FormInterface $form
     * @param Request       $request
     */
    public function __construct(FormInterface $form, Request $request, UserInterface $user)
    {
        $this->form = $form;
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return $response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return $user
     */
    public function getUser()
    {
        return $this->user;
    }
}