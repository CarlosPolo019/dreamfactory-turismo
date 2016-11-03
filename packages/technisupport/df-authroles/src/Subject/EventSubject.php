<?php
namespace TechniSupport\DreamFactory\AuthRoles\Subject;
use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;

/**
 * Class EventSubject Clase que genera los eventos a notificar
 * 
 * @package TechniSupport\DreamFactory\AuthRoles\Subject
 */
class EventSubject extends BaseSubject
{
    /**
     * @var array Información de la ruta
     */
    private $ruta;

    /**
     * @var string Método usuado en la petición
     */
    private $metodo;

    /**
     * @var string Servicio solicitado
     */
    private $servicio;

    /**
     * @var string Acción Solicitada
     */
    private $accion;

    /**
     * @var sring Entidad Solicitada
     */

    private $entidad;

    /**
     * @var sring Elemento Solicitado
     */

    private $elemento;

    /**
     * @var mixed Plataforma DreamFactory
     */
    private $dfPlatform;

    /**
     * @var mixed Evento DreamFactory
     */
    private $dfEvent;

    /**
     * @var array Arreglo de observador es
     */
    private $storage = [];

    /**
     * @var array Arreglo de servicios
     */
    private $services = [];

    /**
     * EventSubject constructor.
     * @param mixed $platform Plataforma DreamFactory
     * @param mixed $event Evento DreamFactory
     */
    public function __construct(&$platform, &$event)
    {
        $this->dfPlatform = $platform;
        $this->dfEvent =& $event;
        $this->ruta = parse_url($_SERVER["REQUEST_URI"]);
        $this->metodo = $_SERVER["REQUEST_METHOD"];
    }

    /**
     * Permite agregar un observador al sujeto
     *
     * @param BaseObserver $observer_in
     * @return mixed
     */
    public function attach(BaseObserver &$observer_in)
    {
        $this->storage[$observer_in->id()]=$observer_in;
    }

    /**
     * Quita un observador del sujeto
     *
     * @param BaseObserver $observer_in
     * @return mixed
     */
    public function detach(BaseObserver &$observer_in)
    {
        if(array_key_exists($observer_in->id(), $this->storage )){
            unset($this->storage[$observer_in->id]);
        }
    }

    /**
     * Notifica a los observadores de los eventos
     *
     * @return mixed
     */
    function notify()
    {
        foreach ($this->storage as $store){

           // file_put_contents("/tmp/auobtener.log", json_encode($this), FILE_APPEND );/* @var $store BaseObserver */
            // file_put_contents("/tmp/auobtener.log", json_encode(get_class($store)), FILE_APPEND );
            $store->update($this);
        }
    }

    /**
     * Retorna la plataforma dreamfactory
     *
     * @return mixed
     */
    public function getDfPlatform()
    {
        return $this->dfPlatform;
    }

    /**
     * Establece la pataforma dreamfactory
     *
     * @param mixed $dfPlatform
     * @return EventSubject
     */
    public function setDfPlatform(&$dfPlatform)
    {
        $this->dfPlatform = $dfPlatform;
        return $this;
    }

    /**
     * Retorna el evento dreamfactory
     *
     * @return mixed
     */
    public function getDfEvent()
    {
        return $this->dfEvent;
    }

    /**
     * Establece el evento dreamfactory
     *
     * @param mixed $dfEvent
     * @return EventSubject
     */
    public function setDfEvent(&$dfEvent)
    {
        $this->dfEvent = $dfEvent;
        return $this;
    }

    /**
     * Ejecuta la captura de eventos y notifica
     */
    public function run(){
        $partesRuta = explode("/",$this->ruta["path"]);
        if(isset($partesRuta[3])){
            $this->servicio = $partesRuta[3];
        }

        if(isset($partesRuta[4])){
            $this->accion = $partesRuta[4];
        }

        if(isset($partesRuta[5])){
            $this->entidad = $partesRuta[5];
        }

        if(isset($partesRuta[6])){
            $this->elemento = $partesRuta[6];
        }

        switch ($this->metodo){
            case "POST":
            case "GET":
            case "DELETE":
            case "PUT":
            case "PATCH":
                break;
            default:
                throw new \Exception("METHOD NOT ALLOWED");
        }
        $this->notify();
    }

    /**
     * Returna la ruta solicitada
     *
     * @return array La ruta solicitada
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Establece la ruta solicitada
     *
     * @param array $ruta La ruta solicitada
     * @return EventSubject La instancia del objeto
     */
    public function setRuta(&$ruta)
    {
        $this->ruta = $ruta;
        return $this;
    }

    /**
     * Retorna el método empleado en la petición
     *
     * @return string
     */
    public function getMetodo()
    {
        return $this->metodo;
    }

    /**
     * Establece el método empleado en la petición
     *
     * @param string $metodo Método empleado en la petición
     * @return EventSubject La instancia del objeto
     */
    public function setMetodo(&$metodo)
    {
        $this->metodo = $metodo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * @param mixed $servicio
     */
    public function setServicio($servicio)
    {
        $this->servicio = $servicio;
    }

    /**
     * @return mixed
     */
    public function getAccion()
    {
        return $this->accion;
    }

    /**
     * @param mixed $accion
     */
    public function setAccion(&$accion)
    {
        $this->accion = $accion;
    }

    /**
     * @return mixed
     */
    public function getElemento()
    {
        return $this->elemento;
    }

    /**
     * @param mixed $elemento
     */
    public function setElemento(&$elemento)
    {
        $this->elemento = $elemento;
    }

    /**
     * @return sring
     */
    public function getEntidad()
    {
        return $this->entidad;
    }

    /**
     * @param sring $entidad
     * @return EventSubject
     */
    public function setEntidad(&$entidad)
    {
        $this->entidad = $entidad;
        return $this;
    }

}