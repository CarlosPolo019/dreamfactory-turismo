<?php

namespace TechniSupport\DreamFactory\TPaga\Eventos;



class ListarTarjeta extends  EventoBase {

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
        $post=$api->post;

        $patch=$api->patch;
        $datos=$get("airlinku/_table/usuario?filter=user_id%3D".$user["id"]);

        if($datos["status_code"]==200 && isset($datos["content"]) && isset($datos["content"]["resource"])
            && count($datos["content"]["resource"])>0){
            $usuario = $datos["content"]["resource"][0];
            $idUsuario=$usuario["id"];
            $tpagaUserId=$datos["content"]["resource"][0]["tpaga_customer_id"];
            $tarjetas=$get("airlinku/_table/tarjetas_usuario?filter=usuario_id%3D".$idUsuario);
            if($tarjetas["status_code"]==200 && isset($tarjetas["content"]) && isset($tarjetas["content"]["resource"])) {
                $cards = array();
                //{"token":"TOKEN DE LA TARJETA", "tipo": "TIPO", "ultimos_cuatro":"XXXX","nombre":"NOMBRE","fecha_vencimiento":"XX/YY"}

                foreach($tarjetas["content"]["resource"] as $tarjeta) {
                    $cards[]=[
                        "token" => $tarjeta["tpaga_id"],
                        "tipo" => $tarjeta["type"],
                        "ultimos_cuatro" => $tarjeta["last_four"],
                        "nombre" => $tarjeta["card_holder_name"],
                        "fecha_vencimiento" => $tarjeta["expiration_month"]."/".$tarjeta["expiration_year"],
                    ];
                }
                header("Content-Type: application/json");
                echo json_encode($cards);exit;
            }
        }

    }
}