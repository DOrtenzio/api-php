<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

foreach($_SERVER as $chiave=>$valore){ //info del server per debug iniziale
    echo $chiave."-->".$valore."\n<br>";
}


$metodo=$_SERVER["REQUEST_METHOD"]; //metodo usato
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri ); //array url

$ct=$_SERVER["CONTENT_TYPE"]; //Content-Type: application/json
$type=explode("/",$ct); //[1]=json o xml

$retct=$_SERVER["HTTP_ACCEPT"]; //Accept: application/xml come si vuole la richiesta in formato
$ret=explode("/",$retct);
echo $type[1];

if ($metodo=="GET"){
    echo "get";       
}
if ($metodo=="POST"){
    echo "post\n";

    $body=file_get_contents('php://input'); //stream speciale di PHP che contiene i dati grezzi inviati dal client nel body della richiesta
    echo $body;
   
    if ($type[1]=="json"){
        $data = json_decode($body,true);
    }
    if ($type[1]=="xml"){
        $xml = simplexml_load_string($body);
        $json = json_encode($xml);
        $data = json_decode($json, true);
    }

    $data["valore"]+=2000; //interazione d'esempio

    //RISPOSTA

    header("Content-Type: ".$retct);
    if ($ret[1]=="json"){ //a seconda di riciesta precedente
        echo json_encode($data);
    }
    if ($ret[1]=="xml"){
        $xml = new SimpleXMLElement('<root/>');
        array_walk_recursive($data, array ($xml, 'addChild'));    
        echo $xml->asXML();
        /* $r='<?xml version="1.0"?><rec><nome>'.$data["nome"].'</nome><valore>'.$data["valore"].'</valore></rec>'; */
    }
   
}
if ($metodo=="PUT"){
    echo "put";
    http_response_code(404);
}
if ($metodo=="DELETE"){
    echo "delete";
    http_response_code(404);
}
?>