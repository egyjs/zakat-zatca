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
use Salla\ZATCA\GenerateQrCode;
use Salla\ZATCA\Tags\InvoiceDate;
use Salla\ZATCA\Tags\InvoiceTaxAmount;
use Salla\ZATCA\Tags\InvoiceTotalAmount;
use Salla\ZATCA\Tags\Seller;
use Salla\ZATCA\Tags\TaxNumber;

// check if valid request by checking if the request HTTP_X_RAPIDAPI_HOST contains the string "rapidapi"
function checkIfValidRequest(): bool|string
{
    if (!isset($_GET['superuser'])) {
        if (!isset($_SERVER['HTTP_X_RAPIDAPI_HOST']) || !isset($_SERVER['HTTP_X_RAPIDAPI_USER'])) {
            logRequest(['error_code' => '1', '$_REQUEST' => $_REQUEST, '$_POST' => $_POST]);
            jsonResponse(['message' => '(1) Invalid API key. Go to https://rapidapi.com/egyjs.com@gmail.com/api/qr-code-for-saudi-arabia-zakat-zatca1/ for more info.'], 401);
        } else if (!str_contains($_SERVER['HTTP_X_RAPIDAPI_HOST'], 'rapidapi')) {
            logRequest(['error_code' => '2', '$_REQUEST' => $_REQUEST, '$_POST' => $_POST]);
            jsonResponse(['message' => '(2) Invalid API key. Go to https://rapidapi.com/egyjs.com@gmail.com/api/qr-code-for-saudi-arabia-zakat-zatca1/ for more info.'], 401);
        }
    }
    logRequest();
    return true;
}

function logRequest($error = null){
    $logDir = 'rapidapi_log/';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    if ($error != null) {
        $logErrorDir = $logDir . 'error/';
        if (!file_exists($logErrorDir)) {
            mkdir($logErrorDir, 0777, true);
        }
        $logFile = $logErrorDir . date('Y-m-d') . '.log';
        $line = date('Y-m-d H:i:s') . ' - ' . (is_array($error) ?json_encode($error):$error) . PHP_EOL;
    }else{
        $logFile = $logDir . date('Y-m-d') . '.log';
        $user = @$_SERVER['HTTP_X_RAPIDAPI_USER'];
        $date = date('Y-m-d H:i:s');
        $requestBody = json_encode(['body'=>$_POST,'server'=>$_SERVER]);
        $ip = $_SERVER['X-Forwarded-For'] ?? $_SERVER['REMOTE_ADDR'];
        $line = "User: $user, IP: $ip, Date: $date, Request: $requestBody\n";
    }
    $log = fopen($logFile, 'a');
    fwrite($log, $line);
    fclose($log);
}
function setTimeZone($timezone = null) {
    if ($timezone) {
        date_default_timezone_set($timezone);
        return;
    }
    // api to get user location from ip
    $ip = $_SERVER['X-Forwarded-For'] ?? $_SERVER['REMOTE_ADDR'];
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

function generateHash(array $qrData): string
{
    // use array map with inline fn
    $array = [];
    foreach ($qrData as $key => $value) {
        $key = in_array($key, ['name','sellerName'])? Seller::class : $key;
        $key = in_array($key, ['rn','vatRegistrationNumber'])? TaxNumber::class : $key;
        $key = in_array($key, ['time','timestamp'])? InvoiceDate::class : $key;
        $key = in_array($key, ['total','totalWithVat'])? InvoiceTotalAmount::class : $key;
        $key = $key == 'vat' ? InvoiceTaxAmount::class : $key;
        $array[] = new $key($value);
    }

    return GenerateQrCode::fromArray($array)->toBase64();
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
    $qrDataAsBase64 = trim(generateHash($qrData));
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
