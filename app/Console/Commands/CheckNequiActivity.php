<?php

namespace DreamFactory\Console\Commands;

use Illuminate\Console\Command;
use DB;

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
       //$apiKey="1af7dde94da21c28f5795956b78e6d87bf61e2d86bdf313723613a3b2d8cd564";
        $nequiMovimientos = DB::table('airlink_u.reserva')
            ->select('id','id_usuario')
            ->where('estado', 2)
           ->whereNull('eliminado')
           ->whereNull('eliminado_por')
            ->get();

	
	if(count($nequiMovimientos)> 0 ){

	
        foreach ($nequiMovimientos as $key => $nequiMovimiento) {
        $id=$nequiMovimiento->id;
	$usuario=$nequiMovimiento->id_usuario;
	
        DB::table('airlink_u.reserva')
                    ->where('id', $id)
                    ->update(['estado' => -1]);
	

                // $body="La transacción ha sido cancelada o rechazada";
                // $this->enviarNotificacion($usuario,$body);
        //    $this->actualizarPago($id,$usuario,2,0,0);
           }

       }else{
	$this->info("No tiene reservas tipo 2");
	}
	}
 
      // ESTA ES LA DE QWERTY(DAVID) /*curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json','Authorization: Key=AAAAMzQV9_w:APA91bFKirrdXEvhDIWB5pSBtpe6W52Ov7sqn-X5p8fmD7n-YSYcJbiO53tAJnoXvB1ggxADa30Efyt613rRuLORXnwOtGwYqNCbXTpAUfLZ7wUbAA67crkH6iWwRDtosW-4BIwXGipO'));*/
}
