<?php

namespace DreamFactory\Console\Commands;

use Illuminate\Console\Command;
use DB;
class CheckReservas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cancelar:reservas';

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
           $usuarioReservas = DB::table('turismo.reserva')
            ->select('id','id_usuario')
            ->where('estado', 2)
           ->whereNull('eliminado')
           ->whereNull('eliminado_por')
            ->get();


        if(count($usuarioReservas)> 0 ){


        foreach ($usuarioReservas as $key => $reserva) {
        $id=$reserva->id;
        $usuario=$reserva->id_usuario;

        DB::table('turismo.reserva')
                    ->where('id', $id)
                    ->update(['estado' => -1]);


                // $body="La transacción ha sido cancelada o rechazada";
                // $this->enviarNotificacion($usuario,$body);
        //    $this->actualizarPago($id,$usuario,2,0,0);
           }

       }else{
        $this->info("No tiene reservas tipo 2");
        }
  
	$this->info("This Worked");
	$this->info(now());
    }
}
