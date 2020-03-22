<?php

if (!array_key_exists('path', $_GET)) {
    echo 'Error. Path missing.';
    exit;
}

$path = explode('/', $_GET['path']);
if (count($path) == 0 || $path[0] == "") {
    echo 'Error. Path missing.';
    exit;
}
$param1 = "";
if (count($path) > 1) {
    $param1 = $path[1];
}


$json = ['produtos' => getArrayTxt()];

$method = $_SERVER['REQUEST_METHOD'];

header('Content-type: application/json');
$body = file_get_contents('php://input');

function findById($vector, $param1)
{
    $encontrado = -1;
    foreach ($vector as $key => $obj) {
        if ($obj['id'] == $param1) {
            $encontrado = $key;
            break;
        }
    }
    return $encontrado;
}

if ($method === 'GET') {
    if ($json[$path[0]]) {
        if ($param1 == "") {
            echo json_encode($json[$path[0]]);
        } else {
            $encontrado = findById($json[$path[0]], $param1);
            if ($encontrado >= 0) {
                echo json_encode($json[$path[0]][$encontrado]);
            } else {
                echo 'ERROR';
                exit;
            }
        }
    } else {
        echo [];
    }
}

if ($method === 'POST') {
    $jsonBody = json_decode($body, true);
    $encontrado = findById($json[$path[0]], $jsonBody['id']);
    if ($encontrado >= 0) {
        echo json_encode($json[$path[0]][$encontrado]);
        $stringAux = $jsonBody['id'] . "," . $jsonBody['nome'] . "," . $jsonBody['quantidade'] . ",\n";
        gravar($stringAux);
    } else {
        echo json_encode('Esse produto não está cadastrado no banco de dados.');
        exit;
    }
}


function gravar($texto)
{
    date_default_timezone_set('America/Bahia');
    $dateTime = date('d M Y g-i-s-a');

    $arquivo = "balanco($dateTime).txt";

    if (file_exists($arquivo)) {
        unlink($arquivo);
    }

    $fp = fopen($arquivo, "a+");

    fwrite($fp, $texto);

    fclose($fp);
}

function getArrayTxt()
{
    $arquivo = "produtos.txt";

    $result = fopen($arquivo, "r");

    $dbdata = array();

    for ($i = 0; !feof($result); $i++) {
        $row = fgets($result, 4096);
        if ($row != null) {
            $row = trim($row);
            $rowAux = explode(',', $row);
            $dbdata[] = ['id' => $rowAux[0], 'nome' => $rowAux[1], 'preco' => $rowAux[2]];
        }
    }
    fclose($result);

    return $dbdata;
}

function lerDB()
{
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = '';
    $dbname = 'api';


    $dblink = new mysqli($dbhost, $dbuser, $dbpass, $dbname);


    if ($dblink->connect_errno) {
        printf("Failed to connect to database");
        exit();
    }


    $result = $dblink->query("SELECT * FROM produtos ORDER BY id");


    $dbdata = array();


    while ($row = $result->fetch_assoc()) {
        $dbdata[] = $row;
    }


    header('Content-type: application/json');
    echo json_encode($dbdata);
}
