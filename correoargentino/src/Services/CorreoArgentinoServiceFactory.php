<?php

namespace CorreoArgentino\Services;

use CorreoArgentino\Exceptions\CorreoArgentinoException;
use CorreoArgentino\Utils\CorreoArgentinoUtil;
use CorreoArgentino\Utils\CorreoArgentinoConstants;

class CorreoArgentinoServiceFactory
{
    private $serviceType;

    /**
     * @throws Exception
     * @throws CorreoArgentinoException
     */
    public function __construct()
    {
        $this->serviceType = CorreoArgentinoUtil::getCurrentServiceType();
        if (!isset($this->serviceType)) {
            throw new CorreoArgentinoException("Tipo de servicio indefinido", 1);
        }
    }
    public function get()
    {
        if ($this->serviceType == CorreoArgentinoConstants::MI_CORREO) {
            return new CorreoArgentinoMiCorreoService();
        }
        if ($this->serviceType == CorreoArgentinoConstants::PAQ_AR) {
            return new CorreoArgentinoPaqArService();
        }
    }
}
