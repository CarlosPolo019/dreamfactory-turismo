<?php

namespace TechniSupport\DreamFactory\AuthRoles;
use TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU\EntityDelete;
use TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU\UsuarioAirlinkU;
use TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU\UsuarioObtener;
use TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU\ConductorAirlinkU;
use TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU\ReporteAirlinkU;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class AuthRoles Clase de ingreso al control de roles
 *
 * @author TechniSupport SAS
 * @author Carlos Andraus <candraus@technisupport.com>
 * @copyright TechniSupport SAS 2016
 * @package TechniSupport\DreamFactory\AuthRoles
 */
class AuthRoles
{
    /**
     * @var EventSubject Administrador de eventos
     */
    private $eventSubject;

    /**
     * AuthRoles constructor.
     *
     * @param $dfPlatform Plataforma Dreamfactory
     * @param $dfEvent Evento Dreamfactory
     */
    public function __construct(&$dfPlatform, &$dfEvent)
    {     
        $this->eventSubject= new EventSubject($dfPlatform,$dfEvent);

        $this->eventSubject->attach(new UsuarioAirlinkU());
        //$this->eventSubject->attach(new EntityDelete());
        $this->eventSubject->attach(new UsuarioObtener());
        $this->eventSubject->attach(new ConductorAirlinkU());
        $this->eventSubject->attach(new ReporteAirlinkU());

        $_SERVER["REQUEST_METHOD"]="PUT";
        $this->eventSubject->run();
        $dfEvent = $this->eventSubject->getDfEvent();
    }
}
