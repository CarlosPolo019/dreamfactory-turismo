<?php

namespace TechniSupport\DreamFactory\TPaga\Eventos;


/**
 * Class EventoBase
 * @package TechniSupport\DreamFactory\TPaga\Eventos
 */
abstract class EventoBase {
    /**
     * @param $platform
     * @param $event
     * @return mixed
     */
    public abstract function evento($platform, $event);
}