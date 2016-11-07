<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class ConductorAirlinkU Maneja los eventos de creación de usuarios para los conductores
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
        /**
         * @var EventSubject $subject_in
         */
        if($subject_in->getServicio() == "airlinku" && $subject_in->getAccion() == "_table" && $subject_in->getEntidad() == "conductor") {
        
            $dfEvent = $subject_in->getDfEvent();

            if(isset($dfEvent["response"])) {
                $this->postCreacionConductor($subject_in);
            }
            else {
                $this->preCreacionConductor($subject_in);      
            }
        }
    }

    /**
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function preCreacionConductor(BaseSubject &$subject_in) {
        /**
         * @var $subject_in EventSubject
         */
        $get = $subject_in->getDfPlatform()["api"]->get;
        $post = $subject_in->getDfPlatform()["api"]->post;
        $patch = $subject_in->getDfPlatform()["api"]->patch;
        $put = $subject_in->getDfPlatform()["api"]->put;
        $payload = $subject_in->getDfEvent()["request"]["payload"]["resource"][0];      

        //CREANDO USUARIO PARA EL NUEVO CONDUCTOR
        $body = [];
        $body["resource"] = [];

        $body["resource"][] = [
            "email" => $payload["email"],
            "name" => $payload["primer_nombre"]." ".$payload["primer_apellido"], 
            "username" => $payload["email"],  
            "password" => $payload["nro_documento"]
        ];

        $result = $post("system/user", $body);
        $user = $result["content"];
   
        if ($result["status_code"] == 500) {
             throw new \Exception('Correo electronico "'.$payload["email"]." ya se encuentra registrado. Por favor intentelo de nuevo.");
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

        //OBTENIENDO USUARIO DEL CONDUCTOR
        $result1 = $get("system/user?fields=id&filter=".urlencode("email=".$payload["email"]));
        $user = $result1["content"];        
     
        //AGREGANDO ROL AL USUARIO
        $user["resource"][0]["user_to_app_to_role_by_user_id"] = [[
            "user_id" =>  $user["resource"][0]["id"],
            "app_id" => 10,
            "role_id" => 9
        ]];
        
        //AGREGANDO LOOKUP-KEYS PARA EL USUARIO
        $user["resource"][0]["user_lookup_by_user_id"] = [[
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

        $res = $put("system/user",$user);
        
        //ACTUALIZANDO CONDUCTOR CON EL USER_ID
        $result2 = $patch("airlinku/_table/conductor/".$response["content"]["resource"][0]["id"], [ "user_id" => $user["resource"][0]["id"] ]);
        $conductor = $result2["content"];
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
