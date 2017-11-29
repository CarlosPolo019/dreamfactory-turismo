<?php

namespace TechniSupport\DreamFactory\TPaga\Eventos;


class BorrarTarjeta extends  EventoBase {

    /**
     * @param $platform
     * @param $event
     * @return mixed
     */
    public static function evento($platform, $event)
    {
        $user = $platform["session"]["user"];
        $api=$platform["api"];
        $get=$api->get;
        $delete=$api->delete;
        $post=$api->post;

        $patch=$api->patch;
        $token = $event["request"]["payload"]["token"];
        if(trim($token)=="") {
            echo '{"success":false,"mensaje":"Falta Parametro requerido"}';
            exit;
        }
        $datos=$get("airlinku/_table/usuario?filter=user_id%3D".$user["id"]);

        if($datos["status_code"]==200 && isset($datos["content"]) && isset($datos["content"]["resource"])
            && count($datos["content"]["resource"])>0){
            $usuario = $datos["content"]["resource"][0];
            $idUsuario=$usuario["id"];
            $tpagaUserId=$datos["content"]["resource"][0]["tpaga_customer_id"];
            //echo "airlinku/_table/tarjetas_usuario?filter=usuario_id%3D".$idUsuario."%20AND%20tpaga_id%3D".$token;exit;
            $tarjetas=$get("airlinku/_table/tarjetas_usuario?filter=(usuario_id%3D'".$idUsuario."')%20AND%20(tpaga_id%3D'".$token."')");

            if($tarjetas["status_code"]==200 && isset($tarjetas["content"]) &&
                isset($tarjetas["content"]["resource"]) && count($tarjetas["content"]["resource"]) >0 ) {

                //{"token":"TOKEN DE LA TARJETA", "tipo": "TIPO", "ultimos_cuatro":"XXXX","nombre":"NOMBRE","fecha_vencimiento":"XX/YY"}

                foreach($tarjetas["content"]["resource"] as $tarjeta) {

                    $cards=[
                        "token" => $tarjeta["tpaga_id"],
                        "tipo" => $tarjeta["type"],
                        "ultimos_cuatro" => $tarjeta["last_four"],
                        "nombre" => $tarjeta["card_holder_name"],
                        "fecha_vencimiento" => $tarjeta["expiration_month"]."/".$tarjeta["expiration_year"],
                    ];
                    $idTarjeta=$tarjeta["id"];
                }
                //var_dump("airlinku/_table/tarjetas_usuario/$idTarjeta");exit;
                $respuesta=$delete("airlinku/_table/tarjetas_usuario/$idTarjeta");
                header("Content-Type: application/json");
                echo json_encode($cards);exit;
            } else {
                echo '{"success":false,"mensaje":"Tarjeta no encontrada"}';
                exit;
            }
        }

    }
}