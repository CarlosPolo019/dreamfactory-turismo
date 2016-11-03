<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class UsuarioObtener Maneja los eventos de los Usuarios
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU
 */
class UsuarioObtener extends BaseObserver
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
       // file_put_contents("/tmp/auobtener.log", json_encode($subject_in->getServicio()), FILE_APPEND );
        if($subject_in->getServicio()=="auobtener") {
            $session=$subject_in->getDfPlatform()["session"];
            $event=$subject_in->getDfEvent();
            if(!$session["user"]["is_sys_admin"]){
                if( !isset($session["lookup"]["id_universidad"]) || !isset($session["lookup"]["id_usuario"])   ){
                    $subject_in->getDfEvent()["response"]["status_code"]=401;
                    $subject_in->getDfEvent()["response"]["content"] = [];
                    $subject_in->getDfEvent()["response"]["content"]["error"] = "Usuario no autorizado";
                }else{
                    $params = $subject_in->getDfEvent()['request']['parameters'];
                    if(!isset($params["tipo"])){
                        $event["response"]["status_code"]=404;
                        $event["response"]["content"]["error"] = "Falta parámetro tipo";
                    }else{
                        $lookup = $session["lookup"];
                        $id_universidad = $lookup["id_universidad"]*1;
                        $id_usuario = $lookup["id_usuario"]*1;
                        switch($params["tipo"]){
                            case "servicio":
                                $event["response"]["content"]=$this->procesarServicio($subject_in, $id_usuario, $id_universidad);
                                break;
                            case "saldo":
                                $event["response"]["content"]=$this->obtenerSaldo($subject_in, $id_usuario, $id_universidad);
                                break;
                            case "movimientos":
                                $event["response"]["content"]=$this->obtenerMovimientos($subject_in, $id_usuario, $id_universidad);
                                break;
                            case "direccion":
                                break;
                            case "tarjeta":
                                break;
                            default:
                                $event["response"]["status_code"]=404;
                                $event["response"]["content"]["error"] = "Parámetro inválido";
                        }
                    }
                }
            }
            $subject_in->setDfEvent($event);

        }



    }


    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function procesarServicio(BaseSubject &$subject_in,$id_usuario,$id_universidad)
    {
        /**
         * @var EventSubject $subject_in
         */
        switch(strtoupper($subject_in->getDfEvent()["request"]["method"])){
            /**
             * OBTIENE LOS SERVICIOS DISPONIBLES
             */
            case "GET":
                return $this->getServicio($subject_in, $id_usuario, $id_universidad);
                break;
            /**
             * CREA RESERVA
             */
            case "POST":
                return $this->postServicio($subject_in, $id_usuario, $id_universidad);
                break;
            /**
             * ACTUALIZA UNA RESERVA ( Comentarios, Calificación )
             */
            case "PATCH":
                return $this->getServicio($subject_in, $id_usuario, $id_universidad);
                break;
            /**
             * CANCELA RESERVA
             */
            case "DELETE":
                return $this->deleteServicio($subject_in, $id_usuario, $id_universidad);
                break;
        }

    }

    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function obtenerSaldo(BaseSubject &$subject_in,$id_usuario,$id_universidad){
        /**
         * @var EventSubject $subject_in
         */
        $post=$subject_in->getDfPlatform()["api"]->post;
        $subject_in->getDfEvent()["response"]["content"]["resource"]="id_usuario";
        $params = $subject_in->getDfEvent()['request']['parameters'];
        $params = [
            [
                "name"  => 'p_id_usuario',
                "value" => $id_usuario
            ],[
                "name"  => 'p_id_universidad',
                "value" => $id_universidad
            ]
        ];

        $content=$post("airlinku/_func/obtener_saldo", ["params" =>$params]);
        //$content=$post("airlinku/_table/movimiento", ["params" =>$params]);

       //  file_put_contents("/tmp/auobtener.log", json_encode($content), FILE_APPEND );
        $content = ["resource" =>json_decode($content["content"])];
        return $content;
    }

    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function obtenerMovimientos(BaseSubject &$subject_in,$id_usuario,$id_universidad){
        /**
         * @var EventSubject $subject_in
         */
        $get=$subject_in->getDfPlatform()["api"]->get;
        $subject_in->getDfEvent()["response"]["content"]["resource"]="id_usuario";
        $params = $subject_in->getDfEvent()['request']['parameters'];
        $cantidad = $params["cantidad"]?$params["cantidad"]*1:10;
        $desde = $params["desde"]?$params["desde"]*1:0;
        $params = ["params"=>[
            [
                "name"  => 'p_id_universidad',
                "value" => $id_universidad
            ],[
                "name"  => 'p_id_usuario',
                "value" => $id_usuario
            ],
            [
                "name"  => 'p_cantidad',
                "value" => $cantidad
            ],[
                "name"  => 'p_desde',
                "value" => $desde
            ]
        ]];

        $content=$get("airlinku/_proc/obtener_movimientos",$params);
        //$content=$post("airlinku/_table/movimiento", ["params" =>$params]);

       // file_put_contents("/tmp/auobtener.log", json_encode([$content,$params]), FILE_APPEND );
        $content = ["resource" =>$content["content"]];
        return $content;
    }



    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function getServicio(BaseSubject &$subject_in,$id_usuario,$id_universidad){
        /**
         * @var EventSubject $subject_in
         */
        $post=$subject_in->getDfPlatform()["api"]->post;
        $subject_in->getDfEvent()["response"]["content"]["resource"]="id_usuario";
        $params = $subject_in->getDfEvent()['request']['parameters'];
        $params = [
            [
                "name"  => 'p_id_usuario',
                "value" => $id_usuario
            ],[
                "name"  => 'p_id_universidad',
                "value" => $id_universidad
            ],[
                "name"  =>'p_id_direccion_origen',
                "value" => $params["id_direccion_origen"]
            ],[
                "name"  =>'p_id_ubicacion_destino',
                "value" => $params["id_ubicacion_destino"]
            ],[
                "name"  =>'p_id_ubicacion_origen',
                "value" => $params["id_ubicacion_origen"]
            ],[
                "name"  =>'p_id_direccion_destino',
                "value" => $params["id_direccion_destino"]
            ]
        ];

        $content=$post("airlinku/_func/obtener_servicios", ["params" =>$params]);

        //file_put_contents("/tmp/auobtener.log", json_encode($subject_in->getDfPlatform()["session"]["lookup"]), FILE_APPEND );
        $content = ["resource" =>json_decode($content["content"])];
        return $content;
    }

    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function postServicio(BaseSubject &$subject_in,$id_usuario,$id_universidad){
        /**
         * @var EventSubject $subject_in
         */
        $post=$subject_in->getDfPlatform()["api"]->get;
        $subject_in->getDfEvent()["response"]["content"]["resource"]="id_usuario";
        $params = $subject_in->getDfEvent()['request']['parameters'];
        $payload = $subject_in->getDfEvent()['request']["payload"];
        //file_put_contents("/tmp/auobtener.log", json_encode(["PARAMS"=>$params,$payload]), FILE_APPEND );
        if(!isset($payload["id_servicio"]) || is_nan($payload["id_servicio"])) {
            throw new \Exception("Falta parametro id_servicio");
        }else{
            $id_servicio = $payload["id_servicio"]*1;
        }
        if(!isset($payload["id_direccion"]) || is_nan($payload["id_direccion"])) {
            throw new \Exception("Falta parametro id_direccion");
        }else{
            $id_direccion = $payload["id_direccion"]*1;
        }
        $params = [
            [
                "name"  => 'p_id_universidad',
                "value" => $id_universidad
            ],
            [
                "name"  => 'p_id_usuario',
                "value" => $id_usuario
            ],[
                "name"  =>'p_id_servicio',
                "value" => $id_servicio
            ],[
                "name"  =>'p_id_direccion',
                "value" => $id_direccion
            ]
        ];

        $content=$post("airlinku/_proc/crear_reserva", ["params" =>$params]);

       // file_put_contents("/tmp/auobtener.log", json_encode($content), FILE_APPEND );
        $content = ["resource" =>$content["content"]];
        // $content = ["resource" =>[$id_usuario,$id_universidad,$id_servicio,$id_direccion]];
        return $content;
    }


    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function deleteServicio(BaseSubject &$subject_in,$id_usuario,$id_universidad){
        /**
         * @var EventSubject $subject_in
         */
        $post=$subject_in->getDfPlatform()["api"]->get;
        $subject_in->getDfEvent()["response"]["content"]["resource"]="id_usuario";
        $params = $subject_in->getDfEvent()['request']['parameters'];
        $payload = $subject_in->getDfEvent()['request']["payload"];
        //file_put_contents("/tmp/auobtener.log", json_encode(["PARAMS"=>$params,$payload]), FILE_APPEND );
        if(!isset($params["id_reserva"]) || is_nan($params["id_reserva"])) {
            throw new \Exception("Falta parametro id_reserva");
        }else{
            $id_reserva = $params["id_reserva"]*1;
        }
        $params = [
            [
                "name"  => 'p_id_universidad',
                "value" => $id_universidad
            ],
            [
                "name"  => 'p_id_usuario',
                "value" => $id_usuario
            ],[
                "name"  =>'p_id_reserva',
                "value" => $id_reserva
            ]
        ];

        $content=$post("airlinku/_proc/cancelar_reserva", ["params" =>$params]);

        // file_put_contents("/tmp/auobtener.log", json_encode($content), FILE_APPEND );
        $content = ["resource" =>$content["content"]];
        // $content = ["resource" =>[$id_usuario,$id_universidad,$id_servicio,$id_direccion]];
        return $content;
    }

    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function procesarDireccion(BaseSubject &$subject_in,$id_usuario,$id_universidad)
    {

    }

    /**
     * @param BaseSubject $subject_in
     * @param $id_usuario
     * @param $id_universidad
     */
    private function procesarTarjetas(BaseSubject &$subject_in,$id_usuario,$id_universidad)
    {

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
