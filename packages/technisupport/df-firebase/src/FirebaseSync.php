<?php
namespace TechniSupport\DreamFactoryFirebase;
use Firebase\FirebaseInterface;
use Firebase\FirebaseLib;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Log;

/** 
 * Helper class for syncing dreamfactory database services to firebase"
 * 
 * @author Carlos Andraus <candraus@technisupport.com>
 * @authot TechniSupport SAS
 */
class FirebaseSync {
    
    /**
     * @var FirebaseInterface|null
     */
    protected $firebaseClient;

    /**
     * @var array
     */
    protected $platform;

    /**
     * @var array
     */
    protected $event;

    /**
     * @var array
     */
    protected $payload;
    
    protected $logger;

    /**
     * @var string
     */
    protected $schemaName;

    public function __construct($platform,$event,$payload,$schemaName)
    {
        $this->platform=$platform;
        $this->event=$event;
        $this->payload=$payload;
        $this->schemaName = $schemaName;
        if (is_null($this->firebaseClient)) {
            $this->firebaseClient = new FirebaseLib(config('services.firebase.database_url'), config('services.firebase.secret'));
           
        }
	$monolog = Log::getMonolog();
        $monolog->pushHandler(new StreamHandler('/tmp/firebase.log', Logger::DEBUG));
        
    }

    /**
     * Syncs the data to firebase
     *
     * @param $platform Dreamfactory platform var
     * @param $payload Dreamfactory payload var
     * @param $payload Dreamfactory event var
     * @return boolean
     */
    public function sync(){

        /*
         * The resource name to be updated
         */
        $resource =  $this->event["resource"];

        if($resource==null){ throw new \Exception("Not resource specified");
            throw new \Exception("Not resource specified");
        }

        /*
         * Full resource path
         */
        $path = parse_url($resource)["path"];

        /*
        * The path elements
        */
        $path_elements = explode("/",$path);


        /*
         * The base resource
         */
        $base_resource = $path_elements[0];
        /*
         * The resource schema
         */
        $schema=$this->getResourceSchema($base_resource);
        /*
         * Element being updated
         */
        $payload = $this->event["response"]["content"];
        /**
         * Elements id to sync with firebase
         */
        $idsToSync = [];
        if(!isset($payload["resource"]) && isset($payload["id"])){
            $id=$payload["id"];
            $idsToSync[]=$id;
        }else if(isset($payload["resource"])){
            foreach ($payload["resource"] as $elm){
                $id=$elm["id"];
                $idsToSync[]=$id;
            }
        }


        $this->sendToFirebase($idsToSync,$base_resource,$this->event["request"]["method"] , $schema);






        file_put_contents("/tmp/firebase_sync.log",var_export($idsToSync,true),FILE_APPEND);
	    $post = $this->platform["api"]->patch;
	    return true;
    }

    /**
     * Return the schema information for a resource
     *
     * @param $basePath
     * @return mixed
     */
    public function getResourceSchema($basePath)
    {

        $get=$this->platform["api"]->get;
        $info = $get($this->schemaName."/_schema/".$basePath);
        $schema = [];
        $schema["fields"] = "*";
        $relateds=[];
        foreach ($info["content"]["related"] as $related){
            $relateds[]=$related["alias"]?$related["alias"]:$related["name"];
        }
        $schema["related"]=implode(",", $relateds );
        return $schema;
    }

    /**
     * Return the schema information for a resource
     *
     * @param $basePath
     * @return mixed
     */
    public function getResourceInfo($basePath,$id,$schema)
    {

        $get=$this->platform["api"]->get;
        $path=$this->schemaName."/_table/".$basePath."/".$id."?fields=".$schema["fields"]."&related=".$schema["related"];
        file_put_contents("/tmp/firebase_sync.log",var_export($path,true),FILE_APPEND);
        $info = $get($path,[]);
        return $info["content"];
    }

    public function sendToFirebase($idsToSync,$basePath,$method,$schema){
        $path = $this->schemaName."/".$basePath."/";
        file_put_contents("/tmp/firebase_sync.log",var_export($method,true),FILE_APPEND);
        switch ($method){
            case "POST":
                foreach ($idsToSync as $id){
                   $return= $this->firebaseClient->set($path.$id, $this->getResourceInfo($basePath, $id, $schema));
                }
                break;
            case "PUT":
            case "PATCH":
                foreach ($idsToSync as $id){
                    $return = $this->firebaseClient->update($path.$id, $this->getResourceInfo($basePath, $id, $schema));
                }
                break;
            case "DELETE";
                foreach ($idsToSync as $id){
                    $return = $this->firebaseClient->delete($path.$id);
                }
                break;
        }
        file_put_contents("/tmp/firebase_sync.log","\n\n----------------------\n       BASEPATH       \n----------------------\n".var_export($basePath,true),FILE_APPEND);
        file_put_contents("/tmp/firebase_sync.log","\n\n----------------------\n          ID          \n----------------------\n".var_export($id,true),FILE_APPEND);
        file_put_contents("/tmp/firebase_sync.log","\n\n----------------------\n        SCHEMA        \n----------------------\n".var_export($schema,true),FILE_APPEND);
        file_put_contents("/tmp/firebase_sync.log","\n\n----------------------\n     RESOURCE INFO    \n----------------------\n".var_export($this->getResourceInfo($basePath, $id, $schema),true),FILE_APPEND);
        file_put_contents("/tmp/firebase_sync.log","\n\n----------------------\n       RESPUESTA      \n----------------------\n".var_export($return,true),FILE_APPEND);
    }

    /**
     * @param FirebaseInterface|null $firebaseClient
     */
    public function setFirebaseClient($firebaseClient)
    {
        $this->firebaseClient = $firebaseClient;
    }
}
