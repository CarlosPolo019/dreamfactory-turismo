<?php
namespace TechniSupport\DreamFactory\AuthRoles\Subject;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;

/**
 * Class BaseSubject clase base subject para implementar patron observador
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Subject
 * @author TechniSupport SAS
 * @author Carlos Andraus <candraus@technisupport.com>
 * @copyright TechniSupport SAS 2016
 */
abstract class BaseSubject
{
    /**
     * @var int Identificación del Evento
     */
    private $evento;

    /**
     * Permite agregar un observador al sujeto
     *
     * @param BaseObserver $observer_in
     * @return mixed
     */
    abstract function attach(BaseObserver &$observer_in);

    /**
     * Quita un observador del sujeto
     *
     * @param BaseObserver $observer_in
     * @return mixed
     */
    abstract function detach(BaseObserver &$observer_in);

    /**
     * Notifica a los observadores de los eventos
     *
     * @return mixed
     */
    abstract function notify();

    /**
     * Retorna el evento generado
     *
     * @return mixed
     */
    public function getEvento()
    {
        return $this->evento;
    }

    /**
     * Establece el evento generado
     *
     * @param mixed $evento
     * @return EventSubject
     */
    public function setEvento(&$evento)
    {
        $this->evento = $evento;
        return $this;
    }
}