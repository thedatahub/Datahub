<?php

namespace DataHub\UserBundle\Util;

interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken();
}