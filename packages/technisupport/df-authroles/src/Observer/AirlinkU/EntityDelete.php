<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class EntityDelete Maneja los eventos de eliminación de entidades
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU
 */
class EntityDelete extends BaseObserver
{
    /**
     * Obtiene la información de ún sujeto
     *
     * @param BaseSubject $subject_in
     * @return mixed
     */
    function update(BaseSubject &$subject_in)
    {
        /**
         * @var EventSubject $subject_in
         */
        $dfEvent = $subject_in->getDfEvent();

        if(!isset($dfEvent["response"]) && $subject_in->getServicio()=="airlinku") {
            $subject_in = $this->entityDelete($subject_in);
        }

    }

    /**
     * Cambia el metodo de borrado
     *
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function entityDelete(BaseSubject &$subject_in){
        /**
         * @var $subject_in EventSubject
         */

        if( strtoupper($subject_in->getDfEvent()["request"]["method"])=="DELETE"){
            $event=$subject_in->getDfEvent();
            $event["request"]["method"]="PATCH";
            $subject_in->setDfEvent($event);
            $payload=[];
            $payload["eliminado"]=date("Y-m-d h:i:s");
            $payload["eliminado_por"]="1";
            $subject_in->getDfEvent()["request"]["payload"]=$payload;

            return $subject_in;
        }
    }

    /**
     * Retorna el id del observador
     * @return string
     */
    function id()
    {
        return md5(__NAMESPACE__.__CLASS__);
    }
}
