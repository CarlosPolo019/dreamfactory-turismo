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
            ->select('id', 'user_id', 'valor', 'transactionId')
            ->where('estado', 1)
            ->whereNull('eliminado')
            ->whereNull('eliminado_por')
            ->get();

        foreach ($nequiMovimientos as $key => $nequiMovimiento) {

        $id=$nequiMovimiento->id;
        $valor=$nequiMovimiento->valor;
        $user_id=$nequiMovimiento->user_id;

        $payload = [
            'action' => 'verificar_push',
            'user_id' => $nequiMovimiento->user_id ,
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
                }else if($status === "0"){
                    //Rechazaron el pago
                    $status = 3;
                }
                $this->actualizarPago($id,$user_id,$status,$valor);
            }

        }
    }

 private function actualizarPago($id,$user_id,$status,$valor){

        switch ($status) {
            case 2:
                  $userContent = DB::table('airlink_u.usuario')
                    ->where('user_id', $user_id)
                    ->first();

                DB::table('airlink_u.nequi_movimiento')
                    ->where('id', $id)
                    ->update(['estado' => 2]);


                DB::table('airlink_u.movimiento')->insert([
                    [
                        'id_usuario' => $userContent->id,
                        'id_tipo_movimiento' => 1,
                        'valor' => $valor,
                        'medio_pago' => 'D',
                        'estado' => 1,
                        'creado_por'=> 1,
                        'actualizado_por'=> 1
                    ]
                ]);
                $this->enviarNotificacion($userContent->id, $valor);

            break;
        }
 }

 private function enviarNotificacion($user_id, $valor){
        $usuario = DB::table('airlink_u.usuario')
            ->where('id', $user_id)
            ->first();
        $user_token = $usuario->firebase_token;

        $title="Recarga Nequi";
        $body="Se ha recargado la cuenta con $".$valor;

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
}
