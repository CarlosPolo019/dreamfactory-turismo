<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU;

use TechniSupport\DreamFactory\AuthRoles\Events\Event;
use TechniSupport\DreamFactory\AuthRoles\Observer\BaseObserver;
use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;
use TechniSupport\DreamFactory\AuthRoles\Subject\EventSubject;
use Excel;

/**
 * Class UsuarioObtener Maneja los eventos de los Usuarios
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer\AirlinkU
 */
class ReporteAirlinkU extends BaseObserver
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

        $dfEvent = $subject_in->getDfEvent();
        
        if($subject_in->getServicio()=="reportes") {
           
            $session=$subject_in->getDfPlatform()["session"];
            $event=$subject_in->getDfEvent();
            
            //if(!$session["user"]["is_sys_admin"]) {
                
                $params = $subject_in->getDfEvent()['request']['parameters'];

                if(!isset($params["tipo"])) {
                    $event["response"]["status_code"] = 404;
                    $event["response"]["content"]["error"] = "Falta parametro tipo";
                }
                else {                    
                    switch($params["tipo"]){
                        case "servicios": 
                        case "movimientos":
                        case "obtener_movimientos_caja":
                        case "obtener_historial_pasajero":
                        case "obtener_listado_servicios":
                        case "obtener_movimientos_caja":
                        case "obtener_servicios_conductor":
                            $event["response"]["content"]=$this->generarReporte($subject_in, $params["tipo"]);
                            break; 
                        default:
                            $event["response"]["status_code"] = 404;
                            $event["response"]["content"]["error"] = "Parametro invalido";
                    }
                }
            //}

            $subject_in->setDfEvent($event);
        }
    }


    /**
     * @param BaseSubject $subject_in     
     */
    private function generarReporte(BaseSubject &$subject_in, $tipo)
    {
        /**
         * @var EventSubject $subject_in
         */
        if (strtoupper($subject_in->getDfEvent()["request"]["method"]) === "GET") {
            
            $excel = new \PHPExcel(); 
            $sheet = $excel->getSheet(0);    
            $sheet->setTitle("REPORTE");
            $excel->setActiveSheetIndex(0); 
 
            $get = $subject_in->getDfPlatform()["api"]->get;

            $params = [];
     
            switch ($tipo) {
                case "obtener_movimientos_caja":
                    $idCaja = $subject_in->getDfEvent()['request']['parameters']["idCaja"];

                    $params = [
                        [
                            "name"  => 'p_id_caja',
                            "value" => $idCaja
                        ]
                    ];

                    break;
                case "obtener_historial_pasajero":
                    $idUsuario = $subject_in->getDfEvent()['request']['parameters']["idUsuario"];

                    $params = [
                        [
                            "name"  => 'p_id_usuario',
                            "value" => $idUsuario
                        ]
                    ];

                    break;  
                case "obtener_listado_servicios":
                    $idUniversidad = $subject_in->getDfEvent()['request']['parameters']["idUniversidad"];
                    $idConductor = $subject_in->getDfEvent()['request']['parameters']["idConductor"];
                    $idEmpresa = $subject_in->getDfEvent()['request']['parameters']["idEmpresa"];
                    $origen = $subject_in->getDfEvent()['request']['parameters']["origen"];
                    $destino = $subject_in->getDfEvent()['request']['parameters']["destino"];

                    $params = [
                        [
                            "name"  => 'p_id_universidad',
                            "value" => $idUniversidad
                        ],
                        [
                            "name"  => 'p_id_conductor',
                            "value" => $idConductor
                        ],
                        [
                            "name"  => 'p_id_empresa',
                            "value" => $idEmpresa
                        ],
                        [
                            "name"  => 'p_origen',
                            "value" => $origen
                        ],
                        [
                            "name"  => 'p_destino',
                            "value" => $destino
                        ]
                    ];

                    break;
                case "obtener_movimientos_caja":
                    $idCaja = $subject_in->getDfEvent()['request']['parameters']["idCaja"];

                    $params = [
                        [
                            "name"  => 'p_id_caja',
                            "value" => $idCaja
                        ]
                    ];

                    break;  
                case "obtener_servicios_conductor":
                    $idConductor = $subject_in->getDfEvent()['request']['parameters']["idConductor"];
                    $idUniversidad = $subject_in->getDfEvent()['request']['parameters']["idUniversidad"];
                    $idEmpresa = $subject_in->getDfEvent()['request']['parameters']["idEmpresa"];

                    $params = [
                        [
                            "name"  => 'p_id_conductor',
                            "value" => $idConductor
                        ],
                        [
                            "name"  => 'p_id_universidad',
                            "value" => $idUniversidad
                        ],                       
                        [
                            "name"  => 'p_id_empresa',
                            "value" => $idEmpresa
                        ]
                    ];

                    break;                
            }
     
            $funcion = $get("airlinku/_proc/".$tipo, ["params" =>$params]);            
            $funcionResult = $funcion["content"];                
            $reporteInfo = array_keys($funcionResult[0]);
            //file_put_contents("/tmp/reporte.log", json_encode($funcionResult), FILE_APPEND ); exit;
            if ($reporteInfo == null) { $reporteInfo = ["No se encontró información disponible para este reporte."]; }

            $excel->getActiveSheet()->fromArray($reporteInfo, null, "A1");
            $excel->getActiveSheet()->fromArray($funcionResult, null, "A2");

            //Agregando estilos al reporte
            $highestColumn = $excel->getActiveSheet()->getHighestColumn();
            $excel->getActiveSheet()->getStyle('A1:'.$highestColumn.'1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('418be1');
            $excel->getActiveSheet()->getStyle('A1:'.$highestColumn.'1')->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE);
            $excel->getActiveSheet()->getStyle('A1:'.$highestColumn.'1')->getFont()->setBold(true);
            $excel->getActiveSheet()->setAutoFilter('A1:'.$highestColumn.'1');

            for ($column = 'A'; $column <= $highestColumn; $column++) {
               $excel->getActiveSheet()->getColumnDimension($column)->setWidth(25); 
            }

            $writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="PRUEBA_'.date('Ymd').'.xlsx"');
            header("Access-Control-Allow-Origin: *");
            $tmpFile = '/tmp/reporte'.rand(10000,99999).'.xlsx';
            $writer->save($tmpFile);
            $content = base64_encode(file_get_contents($tmpFile));      
            echo $content; 
        }
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
