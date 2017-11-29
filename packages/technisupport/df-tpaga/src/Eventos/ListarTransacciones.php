<?php

namespace TechniSupport\DreamFactory\TPaga\Eventos;


class ListarTransacciones extends  EventoBase {

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
            $ordenes_compra=$get("airlinku/_table/orden_compra?limit=10&order_by=creado%20DESC&filter=id_usuario%3D".$idUsuario);

            if($ordenes_compra["status_code"]==200 && isset($ordenes_compra["content"]) && isset($ordenes_compra["content"]["resource"])) {
                $ordenes = array();
                //{"token":"TOKEN DE LA TARJETA", "tipo": "TIPO", "ultimos_cuatr1o":"XXXX","nombre":"NOMBRE","fecha_vencimiento":"XX/YY"}

                foreach($ordenes_compra["content"]["resource"] as $orden_compra) {
                    $ordenes[]=[
                        "numero" => $orden_compra["numero"],
                        "motivo" => $orden_compra["motivo"],
                        "valor" => $orden_compra["valor"],
                        "estado" => $orden_compra["estado"],
                        "fecha" => $orden_compra["creado"],
                    ];
                }
                header("Content-Type: application/json");
                echo json_encode($ordenes);exit;
            }
        }

    }
}
