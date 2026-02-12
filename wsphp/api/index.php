<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

$metodo = $_SERVER["REQUEST_METHOD"];
$formatoRichiesto = $_SERVER['HTTP_ACCEPT'] ?? 'application/json';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$array_url_diviso = explode('/', trim($uri, '/'));
$posUser = array_search('user', $array_url_diviso);

if ($posUser === false) {
    inviaRisposta(404, ["errore" => "Scrivi /user"], $formatoRichiesto);
}

$id=null; //Nella mia idea cè user/id
if(isset($array_url_diviso[$posUser + 1])){
    $id=$array_url_diviso[$posUser+1];
}

$dbJSON = leggiFile();

switch ($metodo) {
    case "GET":
        if ($id !== null) {
            if (isset($dbJSON[$id])) {
                inviaRisposta(200, $dbJSON[$id], $formatoRichiesto);
            } else {
                inviaRisposta(404, ["errore" => "Id inesistente"], $formatoRichiesto);
            }
        }
        inviaRisposta(200, $dbJSON, $formatoRichiesto);
        break;

    case "POST":
        $body = file_get_contents('php://input');
        $nuovoUtente = json_decode($body, true);

        $risultatoValida = validaDati($nuovoUtente);
        if ($risultatoValida !== true) inviaRisposta(400, ["errore" => $risultatoValida], $formatoRichiesto);

        if (!$nuovoUtente) inviaRisposta(400, ["errore" => "Dati non inseriti"], $formatoRichiesto);
        else{
            if(empty($dbJSON)) $nuovoId = 0;
            else $nuovoId = max(array_keys($dbJSON)) + 1;
            
            $dbJSON[$nuovoId] = $nuovoUtente;
            scriviNelFile($dbJSON);
            inviaRisposta(201, ["id" => $nuovoId, "messaggio" => "Creato con successo"], $formatoRichiesto);
        }
        break;

    case "PUT":
        if ($id === null || !isset($dbJSON[$id])) inviaRisposta(404, ["errore" => "ID mancante o utente inesistente"], $formatoRichiesto);

        $body = file_get_contents('php://input');
        $nuoviDatiUtente = json_decode($body, true); 
        
        $risultatoValida = validaDati($nuoviDatiUtente);
        if ($risultatoValida !== true) inviaRisposta(400, ["errore" => $risultatoValida], $formatoRichiesto);

        if (!$nuoviDatiUtente) inviaRisposta(400, ["errore" => "Body non valido"], $formatoRichiesto);
        
        $dbJSON[$id] = $nuoviDatiUtente;
        scriviNelFile($dbJSON);
        inviaRisposta(200, ["messaggio" => "Utente aggiornato"], $formatoRichiesto);
        break;

    case "DELETE":
        if ($id === null || !isset($dbJSON[$id])) inviaRisposta(404, ["errore" => "Impossibile eliminare: ID non trovato"], $formatoRichiesto);
        unset($dbJSON[$id]);
        scriviNelFile($dbJSON);
        inviaRisposta(200, ["messaggio" => "Utente eliminato"], $formatoRichiesto);
        break;

    default:
        inviaRisposta(405, ["errore" => "Metodo non supportato"], $formatoRichiesto);
        break;
}

function leggiFile() {
    $file = "data.json";
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    return json_decode(file_get_contents($file), true);
}

function scriviNelFile($data) {
    file_put_contents("data.json", json_encode($data, JSON_PRETTY_PRINT));
}

function arrayToXml($data, &$xmlData) {
    foreach ($data as $key => $value) {
        $keyName = is_numeric($key) ? "user_$key" : $key;
        
        if (is_array($value)) {
            $subnode = $xmlData->addChild($keyName);
            arrayToXml($value, $subnode);
        } else {
            $xmlData->addChild($keyName, htmlspecialchars($value));
        }
    }
}

function inviaRisposta($codice, $payload, $formato) {
    http_response_code($codice);
    
    if (strpos($formato, 'application/xml') !== false) {
        header("Content-Type: application/xml; charset=UTF-8");
        $xml = new SimpleXMLElement('<root/>');
        arrayToXml($payload, $xml);
        echo $xml->asXML();
    } else {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($payload);
    }
    exit;
}

function validaDati($data) {
    $campiObbligatori = ['nome', 'email'];
    
    foreach ($campiObbligatori as $campo) {
        if (!isset($data[$campo]) || empty(trim($data[$campo]))) {
            return "Campo '$campo' mancante o vuoto.";
        }
    }
    if (count($data) > count($campiObbligatori)) return "Richiesta contiene campi non permessi.";
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) return "Formato email non valido.";
    return true; 
}