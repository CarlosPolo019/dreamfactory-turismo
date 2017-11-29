<?php

namespace TechniSupport\DreamFactory\TPaga\Eventos;
use Httpful\Request;
use TechniSupport\DreamFactory\TPaga\TPaga;

class NuevaTarjeta extends  EventoBase {

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
        $token = $event["request"]["payload"]["token"];
        if(trim($token)=="") {
            echo '{"success":false,"mensaje":"Falta Parametro requerido"}';
            exit;
        }
        $datos=$get("airlinku/_table/usuario?filter=user_id%3D".$user["id"]);
        if($datos["status_code"]==200 && isset($datos["content"]) && isset($datos["content"]["resource"])
            && count($datos["content"]["resource"])>0){
            //$idUsuario=$datos["content"][]
            $usuario = $datos["content"]["resource"][0];
            $idUsuario=$usuario["id"];
            $tpagaUserId=$datos["content"]["resource"][0]["tpaga_customer_id"];
            if($tpagaUserId==null || trim($tpagaUserId)==""){
                /**
                 * Creando nuevo Customer en TPAGA
                 */

                $response = \Httpful\Request::post('https://sandbox.tpaga.co/api/customer')
                    ->body('{ "email": "nietol@uninorte.edu.co", "firstName": "Luis", "lastName": "Nieto", "legalIdNumber": "72346610", "merchantCustomerId": "8", "phone": "3016647391" }')
                    ->authenticateWith(TPaga::PRIVATE_KEY, '')
                    ->send();

                /* ACTUALIZANDO USUARIO CON ID DE TPAGA */
                $respuesta = $response->body;
                if($respuesta && $respuesta->id && trim($respuesta->id)!="") {
                    $payload = [
                        "tpaga_customer_id" => trim($respuesta->id)
                    ];
                    $tpagaUserId=trim($respuesta->id);
                    $response = $patch("airlinku/_table/usuario/".$idUsuario,$payload);

                }else {
                    echo '{"success":false,"mensaje":"No se pudo acceder al servicio de pago"}';
                    exit;
                }

            }

            /* AGREGANDO INFORMACION DE TARJETA A TPAGA */
            $client2 = new \http\Client;
            $request2 = new \http\Client\Request;
            $response2 = \Httpful\Request::post("https://sandbox.tpaga.co/api/customer/$tpagaUserId/credit_card_token")
                ->body('{ "token": "'.$token.'", "skipLegalIdCheck": true}')
                ->authenticateWith(TPaga::PRIVATE_KEY, '')
                ->send();


            if($response2->code<200 && $response2->code>=300 ){
                echo '{"success":false,"mensaje":"No se pudo agregar tarjeta al servicio de pago: '.$response2->code.'"}';
                exit;
            }
            $tarjetaTPaga = $response2->body;

            /*AGREGANDO LA INFORMACION DE LA TARJETA A AIRLINKU*/

            $payload = [
                "tpaga_customer_id" => $tpagaUserId,
                "tpaga_id" => $tarjetaTPaga->id,
                "bin" => $tarjetaTPaga->bin,
                "type" => $tarjetaTPaga->type,
                "expiration_month" => $tarjetaTPaga->expirationMonth,
                "expiration_year" => $tarjetaTPaga->expirationYear,
                "last_four" => $tarjetaTPaga->lastFour,
                "card_holder_name" => $tarjetaTPaga->cardHolderName,
                "card_holder_legal_id_number" => $tarjetaTPaga->cardHolderLegalIdNumber,
                "card_holder_legal_id_type" => $tarjetaTPaga->cardHolderLegalIdType,
                "address_line_1" => $tarjetaTPaga->addressLine1,
                "address_line_2" => $tarjetaTPaga->addressLine2,
                "address_city" => $tarjetaTPaga->addressCity,
                "address_state" => $tarjetaTPaga->addressState,
                "address_postal_code" => $tarjetaTPaga->addressPostalCode,
                "address_country" => $tarjetaTPaga->addressCountry,
                "usuario_id" => $idUsuario
            ];
            $response = $post("airlinku/_table/tarjetas_usuario",["resource"=>[$payload]]);

            /*OBTENIENDO LA TARJETA ALMACENADA*/
            if($response["status_code"]==200 && isset($response["content"])
                && isset($response["content"]["resource"]) && count($response["content"]["resource"])>0 ) {
                $tarjetas=$get("airlinku/_table/tarjetas_usuario/".$response["content"]["resource"][0]["id"]);
                $tarjeta = $tarjetas["content"];
                echo json_encode([
                    "token" => $tarjeta["tpaga_id"],
                    "tipo" => $tarjeta["type"],
                    "ultimos_cuatro" => $tarjeta["last_four"],
                    "nombre" => $tarjeta["card_holder_name"],
                    "fecha_vencimiento" => $tarjeta["expiration_month"]."/".$tarjeta["expiration_year"],
                ]);
                exit;
            }else {
                echo '{"success":false,"mensaje":"No se pudo agregar tarjeta informacion erronea: '.$response["status_code"].'"}';
                exit;
            }
           //var_dump($tarjetas,$datos["content"]["resource"][0]);
        }
    }
}
