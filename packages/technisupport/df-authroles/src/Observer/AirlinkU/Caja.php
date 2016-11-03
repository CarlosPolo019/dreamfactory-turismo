<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;

/**
 * Class Usuario Maneja los eventos de la entidad caja
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU
 */
class Caja extends BaseObserver
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
        if(($subject_in->getAccion()=="user" && $subject_in->getServicio()=="system") ||
            ($subject_in->getAccion()=="register" && $subject_in->getServicio()=="user")){
            $dfEvent = $subject_in->getDfEvent();

            if(isset($dfEvent["response"])){
                $this->postCreacionUsuario($subject_in);
            }else{
                $this->preCreacionUsuario($subject_in);
            }
        }
    }

    /**
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function preCreacionUsuario(BaseSubject &$subject_in){
        /**
         * @var $subject_in EventSubject
         */
        if($subject_in->getDfPlatform()["session"]["role"]["name"] == "AIRLINKU_USUARIO"){
            $payload = $subject_in->getDfEvent()["request"]["payload"];
            $correo=explode("@",$payload["email"]);
            $get=$subject_in->getDfPlatform()["api"]->get;
            if(count($correo)==2){
                $idUniversidad = $payload["university_id"];
                if(!$idUniversidad || is_nan($idUniversidad)){
                    throw new \Exception("Debe proporcionar universidad");
                }else{
                    $dominio=$correo[1];
                    $content = $get("airlinku/_table/universidad/".$idUniversidad);
                    if($content && isset($content["status_code"]) && $content["status_code"]=="200"){
                        $universidad=$content["content"];
                        if($universidad["estado"]!="1"){
                            throw new \Exception("Universidad no autorizada");
                        }
                        $dominios = explode(",",$universidad["dominios"]);
                        if(!in_array($dominio,$dominios)){
                            throw new \Exception("dominio no autorizado");
                        }
                    }else{
                        throw new \Exception("Error al crear el registro");
                    }
                }
            }else{
                throw new \Exception("Correo inválido");
            }
        }
    }

    /**
     * @param BaseSubject $subject_in Sujeto generador de eventos
     */
    function postCreacionUsuario(BaseSubject &$subject_in){
        /**
         * @var $subject_in EventSubject
         */
        if($subject_in->getDfPlatform()["session"]["role"]["name"] == "AIRLINKU_USUARIO"){
            $get=$subject_in->getDfPlatform()["api"]->get;
            $post=$subject_in->getDfPlatform()["api"]->post;
            $patch=$subject_in->getDfPlatform()["api"]->patch;
            $put=$subject_in->getDfPlatform()["api"]->put;
            $payload=$subject_in->getDfEvent()["request"]["payload"];
            $response=$subject_in->getDfEvent()["response"];
            $idUniversidad = $payload["university_id"];

            if(!$idUniversidad || is_nan($idUniversidad)){
                throw new \Exception("Debe proporcionar universidad");
            }else{
                $body=[];
                $body["resource"]=[];
                $body["resource"][]=[
                    "id_universidad"    =>  $idUniversidad,
                    "nombres"           =>  $payload["first_name"]." ".$payload["last_name"],
                    "primer_nombre"     =>  $payload["first_name"],
                    "primer_apellido"   =>  $payload["last_name"],
                    "telefono"          =>  $payload["phone"]
                ];
                $content=$post("airlinku/_table/usuario",$body);
                $idUsuario=$content["content"]["resource"][0]["id"];


                $result = $get("system/user?fields=id&filter=".urlencode("email=".$payload["email"]));
                $user=$result["content"];
                $user["resource"][0]["user_lookup_by_user_id"]=[[
                    "name"=>"id_usuario",
                    "value"=>$idUsuario,
                    "private"=>false,
                    "allow_user_update"=>false,
                ],[
                    "name"=>"id_universidad",
                    "value"=>$idUniversidad,
                    "private"=>false,
                    "allow_user_update"=>false,
                ]];
                $res=$put("system/user",$user);
            }
            //file_put_contents("/tmp/log.txt", json_encode($res),FILE_APPEND );
            $idUsuario=$content["content"]["resource"][0]["id"];
        }
        //throw new \Exception("TODO POST CREACION DE USUARIOS");
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