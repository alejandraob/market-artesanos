<?php

namespace CorreoArgentino\Interfaces;

use CorreoArgentino\Interfaces\CorreoArgentinoServiceInterface;

interface CorreoArgentinoServiceMiCorreoInterface extends CorreoArgentinoServiceInterface
{
    public function login();
    public function getRates($postalCode, $dimensions);
}
