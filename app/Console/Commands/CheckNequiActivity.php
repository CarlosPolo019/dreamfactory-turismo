<?php

namespace DreamFactory\Console\Commands;

use Illuminate\Console\Command;
use DB;
use TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU\Nequi;
use Httpful\Request;

class CheckNequiActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nequi:check-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
public function handle()
    {
       $apiKey="1af7dde94da21c28f5795956b78e6d87bf61e2d86bdf313723613a3b2d8cd564";
        $nequiMovimientos = DB::table('airlink_u.nequi_movimiento')
            ->select('id','user_id' ,'creado_por', 'valor', 'transactionId')
            ->where('estado', 1)
            ->whereNull('eliminado')
            ->whereNull('eliminado_por')
            ->get();

        foreach ($nequiMovimientos as $key => $nequiMovimiento) {

        $id=$nequiMovimiento->id;
        $valor=$nequiMovimiento->valor;
	$usuario=$nequiMovimiento->creado_por;
	$user_id=$nequiMovimiento->user_id;
	$code=$nequiMovimiento->transactionId;

        $payload = [
            'action' => 'verificar_push',
            'user_id' => $nequiMovimiento->creado_por,
            'transactionId' => $nequiMovimiento->transactionId ];



         $headers = [
           "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-Dreamfactory-API-Key" => $apiKey
        ];


     $response = Request::post("dreamfactory.technisupport.com/api/v2/nequi")
                ->addHeaders($headers)
                ->body(json_encode($payload))
                ->autoParse(false)
               ->send();

         $status = json_decode($response,true);

            if ($status){
                if ($status === "35"){
                    //pago aprobado
			$status = 2;
		$this->actualizarPago($code,$usuario,$status,$valor,$user_id);
                }else if($status === "3"){
                    //Rechazaron el pago
			$status = 3;
		$this->actualizarPago($code,$usuario,$status,$valor,$user_id);
		}else{
		
		
		}
             //$this->actualizarPago($code,$usuario,$status,$valor,$user_id);
            }

        }
    }

 private function actualizarPago($code,$user_id,$status,$valor,$user){

        switch ($status) {
            case 2:
                  $userContent = DB::table('airlink_u.usuario')
                    ->where('id', $user_id)
                    ->first();

                DB::table('airlink_u.nequi_movimiento')
                    ->where('transactionId', $code)
                    ->update(['estado' => 2]);

	$valorprom = 0;	  	

	if ($valor < 45000){
	$valorprom = $valor;
	}else{
	$valorprom = $valor*1.3;
	}


                DB::table('airlink_u.movimiento')->insert([
                   [
                        'id_usuario' => $user_id,
                        'id_tipo_movimiento' => 9,
                        'valor' => $valorprom,
                        'medio_pago' => 'D',
                        'estado' => 1,
                        'creado_por'=>  $userContent->id,
                        'actualizado_por'=>  $userContent->id
                    ]
	    ]);

		  $body="Se ha recargado la cuenta con $".$valorprom;
		   $this->enviarNotificacion($user_id,$body);
		//$this->notificacionesIOS($user_id,$body);
		break;
	case 3:
                  $userContent = DB::table('airlink_u.usuario')
                    ->where('id', $user_id)
                    ->first();

                DB::table('airlink_u.nequi_movimiento')
                    ->where('transactionId', $code)
                    ->update(['estado' => 3]);

	$body="La transacción ha sido cancelada o rechazada";

              $this->enviarNotificacion($user_id, $body);
		// $this->notificacionesIOS($user_id,$body);

        	break;
        }
 }

 private function enviarNotificacion($user_id, $body){
        $usuario = DB::table('airlink_u.usuario')
            ->where('id', $user_id)
            ->first();
        $user_token = $usuario->firebase_token;

        $title="Recarga Nequi";
      //  $body="Se ha recargado la cuenta con $".$valor;

         $headers = '{
                          "to": "'.$user_token.'"';
          $headers .= ',"content_available": true';
          $headers.= ',"notification":{
                              "title": "'.$title.'",
                              "body": "'.$body.'",
                              "sound": "default"
                          }';
        $headers.='}';


        /*CURL PARA ENVIAR LA NOTIFICACIÓN PUSH*/
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // ESTA ES LA DE QWERTY(DAVID) /*curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json','Authorization: Key=AAAAMzQV9_w:APA91bFKirrdXEvhDIWB5pSBtpe6W52Ov7sqn-X5p8fmD7n-YSYcJbiO53tAJnoXvB1ggxADa30Efyt613rRuLORXnwOtGwYqNCbXTpAUfLZ7wUbAA67crkH6iWwRDtosW-4BIwXGipO'));*/
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Key='."AAAAXySWgPI:APA91bFoqqBFKwH0k4Vgwsl7c12yDzSkeQ0kVsd8MOZuNwrwAluh4vcK0cHMlwLd2R5PFrUviu_N-ZMPCz7gGQen9k3uxmExqa7SK_yX1_v7D5TZIKtenNKf_Q_67as459W5GNPc2CEL",'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        //Edit: prior variable $postFields should be $postfields;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // On dev server only!
        $result = curl_exec($ch);
        /*FINAL DE CURL PARA ENVIAR LA NOTIFICACIÓN PUSH*/


 } 

 private function notificacionesIOS ($user_id,$body) {
	$ch=curl_init("https://fcm.googleapis.com/fcm/send");

$usuario = DB::table('airlink_u.usuario')
            ->where('id', $user_id)
            ->first();
        $token = $usuario->firebase_token;

        $title="Recarga Nequi";

	$notificacion = array('title' => $title,'text' => $body);

	$arrayToSend = array('to'=>$token,'notification'=>$notificacion ,'priority' => 'high');

	$json = json_encode($arrayToSend);

	$headers = array();

	$headers[] = 'Content-Type: application/json';

	$headers[]= 'Authorization: Key='."AAAAXySWgPI:APA91bFoqqBFKwH0k4Vgwsl7c12yDzSkeQ0kVsd8MOZuNwrwAluh4vcK0cHMlwLd2R5PFrUviu_N-ZMPCz7gGQen9k3uxmExqa7SK_yX1_v7D5TZIKtenNKf_Q_67as459W5GNPc2CEL";

	curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");
	curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
	curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
	
	// Send the request
	$response=curl_exec($ch);

	curl_close($ch);

	return $response;	

 }





}
