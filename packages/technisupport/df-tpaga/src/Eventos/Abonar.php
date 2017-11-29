<?php

namespace TechniSupport\DreamFactory\TPaga\Eventos;
use Httpful\Request;
use TechniSupport\DreamFactory\TPaga\TPaga;

class Abonar extends  EventoBase {

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
        if(!isset($event["request"]["payload"]) || !isset($event["request"]["payload"]["valor"]) ||
            !isset($event["request"]["payload"]["valor"]) || !isset($event["request"]["payload"]["valor"])
            || !isset($event["request"]["payload"]["cuotas"])  || $event["request"]["payload"]["cuotas"]*1<1 ||
            $event["request"]["payload"]["valor"]*1<5000 || trim($event["request"]["payload"]["token"])=="" ){
            echo '{"success":false,"mensaje":"Falta Parametro requerido"}';
            exit;
        }
        $token = $event["request"]["payload"]["token"];
        $valor = $event["request"]["payload"]["valor"]*1;
        $cuotas = $event["request"]["payload"]["cuotas"]*1;

        $datos=$get("airlinku/_table/usuario?filter=user_id%3D".$user["id"]);
        if($datos["status_code"]==200 && isset($datos["content"]) && isset($datos["content"]["resource"])
            && count($datos["content"]["resource"])>0){
            //$idUsuario=$datos["content"][]
            $usuario = $datos["content"]["resource"][0];
            $idUsuario=$usuario["id"];
            $tpagaUserId=$datos["content"]["resource"][0]["tpaga_customer_id"];

            /*OBTENIENDO TARJETA ALMACENADA*/
            $tarjetas=$get("airlinku/_table/tarjetas_usuario?filter=(usuario_id%3D'".$idUsuario."')%20AND%20(tpaga_id%3D'".$token."')");
            //echo "airlinku/_table/tarjetas_usuario?filter=(usuario_id%3D'".$idUsuario."')%20AND%20(tpaga_id%3D'".$token."')";exit;
            if($tarjetas["status_code"]==200 && isset($tarjetas["content"]) &&
                isset($tarjetas["content"]["resource"]) && count($tarjetas["content"]["resource"]) >0 ) {

                //{"token":"TOKEN DE LA TARJETA", "tipo": "TIPO", "ultimos_cuatro":"XXXX","nombre":"NOMBRE","fecha_vencimiento":"XX/YY"}
                $cards=array();
                foreach($tarjetas["content"]["resource"] as $tarjeta) {

                    $cards[]=[
                        "token" => $tarjeta["tpaga_id"],
                        "tipo" => $tarjeta["type"],
                        "ultimos_cuatro" => $tarjeta["last_four"],
                        "nombre" => $tarjeta["card_holder_name"],
                        "fecha_vencimiento" => $tarjeta["expiration_month"]."/".$tarjeta["expiration_year"],
                    ];
                    $idTarjeta=$tarjeta["id"];
                }



                // CREANDO ORDEN EN AIRLINKU
                $orden = self::uniqidReal();
                $payload = [
                    "valor"=>$event["request"]["payload"]["valor"]*1,
                    "motivo"=>"Abono a cuenta por aplicación",
                    "id_usuario"=>$idUsuario,
                    "numero" => $orden,
                    "estado" => "EN PROCESO DE VALIDACION",
                    "tarjeta_usuario_id" =>$idTarjeta
                ];

                $response=$post("airlinku/_table/orden_compra",["resource"=>[$payload]]);

                if($response["status_code"]==200 && isset($response["content"]) &&
                    isset($response["content"]["resource"]) && count($response["content"]["resource"]) >0 ) {
                    $idOrden = $response["content"]["resource"][0]["id"];

                    // ABONANDO EN TPAGA
                    $payload2 = [
                        "amount"=>$valor,
                        //"childMerchantId"=> "AIRLINKU",
                        "creditCard"=> $token,
                        "currency"=> "COP",
                        "description"=> "ABONO A CUENTA AIRLINKU ".date("Y-m-d H:i:s"),
                        "iacAmount"=> 0,
                        "installments"=> $cuotas,
                        "orderId"=> $orden,
                        "taxAmount"=> 0,
                        //"thirdPartyId"=> "AIRLINK U",
                        "tipAmount"=> 0
                    ];

                    $response2 = \Httpful\Request::post("https://sandbox.tpaga.co/api/charge/credit_card")
                        ->body(json_encode($payload2))
                        ->authenticateWith(TPaga::PRIVATE_KEY, '')
                        ->send();

                    /* GUARDANDO RESULTADO EN AIRLINKU */
                    $respuesta = $response2->body;
                    if($response2->code == 201 || $response2->code == 402){

                        if($response2->code == 201 ) {
                            $estado = "APROBADA";
                            $autorizacion = $respuesta->transactionInfo->authorizationCode;
                            /*CREANDO MOVIMIENTO EN AIRLINKU*/
                            $payload4 = [
                                "id_usuario" => $idUsuario,
                                "id_tipo_movimiento" => 7,
                                "valor" => $valor,
                                "id_tarjeta_credito" => $idTarjeta,
                                "medio_pago" => 'C',
                                "estado" => 1,
                                "numero_comprobante" => $respuesta->paymentTransaction
                            ];
                            $response=$post("airlinku/_table/movimiento",["resource"=>[$payload4]]);
                            //var_dump($response);exit;

                        }else if($response2->code == 402 ) {
                            $estado = "RECHAZADA ";
                            $autorizacion = "";
                        }
                        $payload3 = [
                            "reteica"=>$respuesta->reteIcaAmount,
                            "reteiva"=>$respuesta->reteIvaAmount,
                            "reterenta"=>$respuesta->reteRentaAmount,
                            "tpaga_fee"=>$respuesta->tpagaFeeAmount,
                            "numero_transaccion"=>$respuesta->paymentTransaction,
                            "tpaga_id" => $respuesta->id,
                            "codigo_autorizacion" => $autorizacion,
                            "estado" => $estado
                        ];
                        $response=$patch("airlinku/_table/orden_compra/".$idOrden,$payload3);

                        echo json_encode([
                            "valor"=> $valor,
                            "estado"=> $estado,
                            "fecha"=> $respuesta->dateCreated,
                            "codigo_autorizacion"=> $autorizacion,
                            "numero_transaccion"=>$respuesta->paymentTransaction
                        ]);exit;
                    } else {
                        echo '{"success":false,"mensaje":"No se pudo realizar la operacion. Intente de nuevo más tarde"}';
                        exit;
                    }



                    var_dump($respuesta);exit;
                }else{
                    echo '{"success":false,"mensaje":"No se pudo crear la orden. Intente de nuevo más tarde"}';
                    exit;
                }
                var_dump($response);exit;
            } else {
                echo '{"success":false,"mensaje":"Tarjeta no encontrada"}';
                exit;
            }

        }


    }

    public static function uniqidReal($lenght = 13) {
        // uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }
}
