<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Max-Age: 3600");

//Parametri richiesta
$metodo = $_SERVER["REQUEST_METHOD"];

if ($metodo === "OPTIONS") {
    http_response_code(200);
    exit(0);
}

$content_type = $_SERVER["CONTENT_TYPE"] ?? null;
if ($content_type !== null) {
    $content_type = explode(';', $content_type)[0];
}
$formatoRichiesto = $_SERVER['HTTP_ACCEPT'] ?? 'application/json';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$array_url_diviso = explode('/', trim($uri, '/'));
$posApi = array_search('api', $array_url_diviso); //http://localhost/wsphp/api/{id - opzionale}

if ($posApi === false) inviaRisposta(404, ["errore" => "Url in formato non corretto"], $formatoRichiesto);

$id = null;
if(isset($array_url_diviso[$posApi + 1])){
    $id = $array_url_diviso[$posApi + 1];
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
        } else {
            inviaRisposta(200, $dbJSON, $formatoRichiesto);
        }
        break;
        
    case "POST":
        $body = file_get_contents('php://input');

        $nuovoUtente = null;
        if($content_type == 'application/json') {
            $nuovoUtente = json_decode($body, true);
        } elseif($content_type == 'application/xml') {
            $xml = simplexml_load_string($body);
            $json = json_encode($xml);
            $nuovoUtente = json_decode($json, true);
        } else {
            inviaRisposta(400, ["errore" => "Formato Non Supportato"], $formatoRichiesto);
        }

        // Validazione dati
        if (!$nuovoUtente || !isset($nuovoUtente["name"]) || !isset($nuovoUtente["age"]) || !isset($nuovoUtente["date"])) {
            inviaRisposta(400, ["errore" => "Dati non inseriti"], $formatoRichiesto);
        }
        if (!is_numeric($nuovoUtente["age"]) || $nuovoUtente["age"] < 0 || $nuovoUtente["age"] > 150) {
            inviaRisposta(400, ["errore" => "Età non valida"], $formatoRichiesto);
        }
        if (!validaData($nuovoUtente["date"])) {
            inviaRisposta(400, ["errore" => "Data non valida"], $formatoRichiesto);
        }
        
        if(empty($dbJSON)) {
            $nuovoId = 0;
        } else {
            $nuovoId = max(array_keys($dbJSON)) + 1;
        }
        
        $dbJSON[$nuovoId] = $nuovoUtente;
        scriviNelFile($dbJSON);

        inviaRisposta(201, ["id" => $nuovoId, "obj" => $nuovoUtente], $formatoRichiesto);
        break;

    case "PUT":
        if ($id === null || !isset($dbJSON[$id])) {
            inviaRisposta(404, ["errore" => "ID mancante o utente inesistente"], $formatoRichiesto);
        }

        $body = file_get_contents('php://input');
        $nuoviDatiUtente = null;
        
        if($content_type == 'application/json') {
            $nuoviDatiUtente = json_decode($body, true);
        } elseif($content_type == 'application/xml') {
            $xml = simplexml_load_string($body);
            $json = json_encode($xml);
            $nuoviDatiUtente = json_decode($json, true);
        } else {
            inviaRisposta(400, ["errore" => "Formato Non Supportato"], $formatoRichiesto);
        }
        
        // Validazione dati
        if (!$nuoviDatiUtente || !isset($nuoviDatiUtente["name"]) || !isset($nuoviDatiUtente["age"]) || !isset($nuoviDatiUtente["date"])) {
            inviaRisposta(400, ["errore" => "Body non valido"], $formatoRichiesto);
        }
        if (!is_numeric($nuoviDatiUtente["age"]) || $nuoviDatiUtente["age"] < 0 || $nuoviDatiUtente["age"] > 150) {
            inviaRisposta(400, ["errore" => "Età non valida"], $formatoRichiesto);
        }
        if (!validaData($nuoviDatiUtente["date"])) {
            inviaRisposta(400, ["errore" => "Data non valida"], $formatoRichiesto);
        }
        
        $dbJSON[$id] = $nuoviDatiUtente;
        scriviNelFile($dbJSON);
        inviaRisposta(200, ["messaggio" => "Utente aggiornato", "obj" => $nuoviDatiUtente], $formatoRichiesto);
        break;

    case "DELETE":
        if ($id === null || !isset($dbJSON[$id])) {
            inviaRisposta(404, ["errore" => "Impossibile eliminare: ID non trovato"], $formatoRichiesto);
        }
        
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
    $contenuto = file_get_contents($file);
    return json_decode($contenuto, true) ?? [];
}

function scriviNelFile($data) {
    file_put_contents("data.json", json_encode($data, JSON_PRETTY_PRINT));
}

function validaData($data) {
    $d = DateTime::createFromFormat('Y-m-d', $data);
    return $d && $d->format('Y-m-d') === $data;
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
    if ($formato == "application/xml") {
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
