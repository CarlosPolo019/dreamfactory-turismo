<?php
namespace TechniSupport\DreamFactory\TPaga;

//use Eventos\NuevaTarjeta;

/**
 * Class TPaga  Clase que maneja las transacciones con TPaga de la aplicacion
 *
 * @author TechniSupport SAS
 * @author Carlos Andraus <candraus@technisupport.com>
 * @copyright TechniSupport SAS 2016
 * @package TechniSupport\DreamFactory\AuthRoles
 */
class TPaga {

    private $platform;

    const PRIVATE_KEY = 'g84gjovkpudc9ofuqb8n8lvjpdbq9bc5';


    private $event;

    /**
     * AuthRoles constructor.
     *
     * @param $dfPlatform Plataforma Dreamfactory
     * @param $dfEvent Evento Dreamfactory
     * @param $operacion Tipo de Evento
     */
    public function __construct($dfPlatform, &$dfEvent, $operacion)
    {
        $this->platform = $dfPlatform;
        $this->event = $dfEvent;

        $user = $this->platform["session"]["user"];
        if(!$user) {
            echo '{"success":false,"mensaje":"El usuario no se encuentra autenticado"}';
            exit;
        }
        $metodo = strtoupper($this->event["request"]["method"]);

        switch ($operacion) {
            case "listartarjeta":
                if($metodo=="GET") Eventos\ListarTarjeta::evento($this->platform,$this->event);
                break;
            case "listar":
                if($metodo=="GET") Eventos\ListarTransacciones::evento($this->platform,$this->event);
                break;
            case "nuevatarjeta":
                if($metodo=="POST") Eventos\NuevaTarjeta::evento($this->platform,$this->event);
                break;
            case "abonar":
                if($metodo=="POST") Eventos\Abonar::evento($this->platform,$this->event);
                break;
            case "borrar":

                if($metodo=="DELETE") Eventos\BorrarTarjeta::evento($this->platform,$this->event);
                break;
        }
        echo '{"success":false,"mensaje":"Metodo no encontrado"}';
        exit;

    }

    /**
     * @return Plataforma
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param Plataforma $platform
     * @return TPaga
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return Evento
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Evento $event
     * @return TPaga
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }
}
