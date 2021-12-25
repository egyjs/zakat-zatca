<?php
// this file is restfull api
// header('Content-Type: application/json');
use Rakit\Validation\Validator;

require_once 'helpers.php';
if (!file_exists('vendor/autoload.php')) throw new Exception('please run "composer install" in the project root');
require_once __DIR__ . '/vendor/autoload.php';

if (!isset($_GET['superuser'])) {
    checkIfValidRequest();
}
setTimeZone();

// validate Form data
// required fields: (sellerName OR name), (vatRegistrationNumber OR rn), (timestamp OR time), (totalWithVat OR total), (vat)
$validator = new Validator();
$validation = $validator->validate($_POST, [
    // only english letters
    'sellerName' => 'required_without:name|regex:/^[a-zA-Z ]+$/',
    'name' => 'required_without:sellerName|regex:/^[a-zA-Z ]+$/',

    'vatRegistrationNumber' => 'required_without:rn',
    'rn' => 'required_without:vatRegistrationNumber',

    'timestamp' => 'required_without:time|date:Y-m-d H:i:s',
    'time' => 'required_without:timestamp|date:Y-m-d H:i:s',

    'totalWithVat' => 'required_without:total',
    'total' => 'required_without:totalWithVat',

    'vat' => 'required',
    'size' => 'integer|min:300',
], [
    'sellerName' => '`sellerName` is required, or you can use `name` instead, must be in English',
    'name' => '`name` is required, or you can use `sellerName` instead, must be in English',

    'vatRegistrationNumber' => '`vatRegistrationNumber` is required or you can use `rn` instead',
    'rn' => '`rn` is required or you can use `vatRegistrationNumber` instead',

    'timestamp' => '`timestamp` is required and should be a valid timestamp or you can use `time` instead',
    'time' => '`time` is required and should be a valid timestamp or you can use `timestamp` instead',

    'totalWithVat' => '`totalWithVat` is required or you can use `total` instead',
    'total' => '`total` is required or you can use `totalWithVat` instead',

    'vat' => '`vat` is required'
]);

// if validation fails return the messages
if ($validation->fails()) {
    $errors = $validation->errors()->toArray();
    jsonResponse(['errors'=>$errors], 400);
}

// get only the required fields from the form
$data = array_filter($_POST, function ($key) {
    return in_array($key, ['sellerName', 'name', 'vatRegistrationNumber', 'rn', 'timestamp', 'time', 'totalWithVat', 'total', 'vat']);
}, ARRAY_FILTER_USE_KEY);
generateQRImageJsonResponse($data,$_POST['size']??'300');
