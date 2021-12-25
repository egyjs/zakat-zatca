<?php
// display all  errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use JetBrains\PhpStorm\NoReturn;

// check if valid request by checking if the request HTTP_X_RAPIDAPI_HOST contains the string "rapidapi"
function checkIfValidRequest(): bool|string
{
    if(!isset($_SERVER['HTTP_X_RAPIDAPI_HOST']) || !isset($_SERVER['HTTP_X_RAPIDAPI_KEY']) || !isset($_SERVER['HTTP_X_RAPIDAPI_USER'])){
        jsonResponse(['message' => 'Invalid API key. Go to https://rapidapi.com/egyjs.com@gmail.com/api/qr-code-for-saudi-arabia-zakat-zatca1/ for more info.'], 401);
    }else if(!str_contains($_SERVER['HTTP_X_RAPIDAPI_HOST'], 'rapidapi')){
        jsonResponse(['message' => 'Invalid API key. Go to https://rapidapi.com/egyjs.com@gmail.com/api/qr-code-for-saudi-arabia-zakat-zatca1/ for more info.'], 401);
    }
    dd($_SERVER['']);
    return true;
}

function setTimeZone($timezone = null) {
    if ($timezone) {
        date_default_timezone_set($timezone);
        return;
    }
    // api to get user location from ip
    $ip = $_SERVER['REMOTE_ADDR'];
    $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
    // set timezone to user location
    if (isset($details->timezone)) {
        date_default_timezone_set($details->timezone);
    }else{
        date_default_timezone_set('Asia/Riyadh');
    }
}

function base_path($path = '') {
    return __DIR__ . '/' . $path;
}

function generateQRImageResponse($qrString, $fileName = 'qrcode', $type = 'png', $size = 300) {

    $writer = ($type == 'png' || $type == 'base64') ? new PngWriter() : new SvgWriter();

    $result = Endroid\QrCode\Builder\Builder::create()
        ->writer($writer)
        ->writerOptions([])
        ->data($qrString)
        ->encoding(new Encoding('UTF-8'))
        ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
        ->size($size)
        ->margin(10)
        ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
        ->build();

    $img = $result->getString();
    // img header

    if ($writer instanceof PngWriter && $type != 'base64') {
        header('Content-Type: image/png');
    } else if ($writer instanceof SvgWriter) {
        header('Content-Type: image/svg+xml');
    }else{
        echo $result->getDataUri();
        exit;
    }
    echo $img;
}

function __exec(array $qrData,$size = 500): bool|string|null
{
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { // if windows
        $node = 'C:\Users\el3za\AppData\Roaming\nvm\v16.8.0\node.exe';
    } else {
        $node = '/home/egyjs/.nvm/versions/node/v17.3.0/bin/node';
    }
    $script = base_path('nodejs/bin/index.js');
    //$node = '/home/hermosa/.nvm/versions/node/v17.2.0/bin/node';
    if (!file_exists($node)) {
        throw new Exception('Node.js is not installed');
    }
    if (!file_exists($script)) {
        throw new Exception('Script not found');
    }
    // implode $qrData to string as arguments
    $args = implode(' ', array_map(function ($v, $k) { return sprintf("--%s=\"%s\"", $k, $v); },
        $qrData,
        array_keys($qrData)
    ));
    // execute node script
    $cmd = "$node $script $args 2>&1";
    $qrString = shell_exec($cmd);
    return $qrString;
}

function url($path = ''): string
{
    // return url with https or http based on request
    // add slash in the beginning of the path if not already exists
    $path = ltrim($path, '/');
    return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/' . $path;
}

function addRandomStringToString($string){
    // get random string
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 10; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    // add random string to string with hiddenWord
    $hiddenWord = rand(0,1)?'NO_Xer':'F0XiR';
    $hiddenWord = $hiddenWord.$randomString.$hiddenWord;
    // convert string to array
    $arrString = str_split($string);
    // add hiddenWord to array in random position in array
    $randomPosition = rand(0,count($arrString)-1);
    $arrString = array_merge(array_slice($arrString, 0, $randomPosition), [$hiddenWord], array_slice($arrString, $randomPosition));

    // convert array to string
    $string = implode('',$arrString);
    return $string;
}
function generateQRImageJsonResponse(array $qrData,$size = 500) {
    // convert $qrData to base64 string
    $qrDataAsBase64 = trim(__exec($qrData));
    $size = $size/2002;

    $qrDataAsBase64 = addRandomStringToString($qrDataAsBase64);

    $response = [
        'data' => [
            'qr_code_png' => ("/qrcode/$size/$qrDataAsBase64/qrcode.png"),
            'qr_code_svg' => ("/qrcode/$size/$qrDataAsBase64/qrcode.svg"),
            'qr_code_base64' => ("/qrcode/$size/$qrDataAsBase64/qrcode.base64"),
        ],
    ];
    jsonResponse($response);
}

// return json response
#[NoReturn] function jsonResponse($data, $status = 200)
{
    header('Content-Type: application/json');
    http_response_code($status);
    $is_error = $status >= 400;
    echo json_encode([
        'success' => !$is_error,
    ]+$data);
    exit;
}
