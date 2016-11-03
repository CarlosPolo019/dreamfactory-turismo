<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class Usuario Maneja los eventos de creación de usuarios
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU
 */
class ConductorAirlinkU extends BaseObserver
{
    /**
     * Obtiene la información de ún sujeto
     *
     * @param BaseSubject $subject_in
     * @return mixed
     */
    function update(BaseSubject &$subject_in) {
       //file_put_contents("/tmp/log.txt", json_encode("Creando conductor!!!!!!!"),FILE_APPEND );
        /**
         * @var EventSubject $subject_in
         */
        if($subject_in->getServicio() == "airlinku" && $subject_in->getAccion() == "_table" && $subject_in->getEntidad() == "conductor") {
        
            $dfEvent = $subject_in->getDfEvent();

            if(isset($dfEvent["response"])) {
                $this->postCreacionConductor($subject_in);
            }
        }
    }

    /**
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function postCreacionConductor(BaseSubject &$subject_in) {
        
        /**
         * @var $subject_in EventSubject
         */

        $get = $subject_in->getDfPlatform()["api"]->get;
        $post = $subject_in->getDfPlatform()["api"]->post;
        $patch = $subject_in->getDfPlatform()["api"]->patch;
        $put = $subject_in->getDfPlatform()["api"]->put;
        $payload = $subject_in->getDfEvent()["request"]["payload"]["resource"][0];        
        $response = $subject_in->getDfEvent()["response"];

        //CREANDO USUARIO PARA EL NUEVO CONDUCTOR
        $body = [];
        $body["resource"] = [];

        $body["resource"][] = [
            "email" => $payload["email"],
            "name" => $payload["primer_nombre"]." ".$payload["primer_apellido"], 
            "username" => $payload["email"],  
            "password" => $payload["nro_documento"]
        ];

        //AGREGANDO LOOKUP KEYS PARA EL USUARIO
        $body["resource"][0]["user_lookup_by_user_id"] = [[
            "name"=>"id_conductor",
            "value"=>$response["content"]["resource"][0]["id"],
            "private"=>false,
            "allow_user_update"=>false,
        ],[
            "name"=>"id_empresa",
            "value"=>$payload["id_empresa"],
            "private"=>false,
            "allow_user_update"=>false,
        ]];

        $result1 = $post("system/user", $body);
        $user = $result1["content"];

        $result2 = $get("system/user?fields=id&filter=".urlencode("email=".$payload["email"]));
        $user = $result2["content"];

        $user["resource"][0]["user_to_app_to_role_by_user_id"] = [[
            "user_id" =>  $user["resource"][0]["id"],
            "app_id" => 10,
            "role_id" => 9
        ]];

        $res = $put("system/user",$user);
        
        //ACTUALIZANDO CONDUCTOR CON EL USER_ID

        $result2 = $patch("airlinku/_table/conductor/".$response["content"]["resource"][0]["id"], [ "user_id" => $user["resource"][0]["id"] ]);
        $conductor = $result2["content"];
        
        //file_put_contents("/tmp/log.txt", json_encode($conductor),FILE_APPEND);
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
