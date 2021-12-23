<?php
// this file will return image
require_once 'helpers.php';
if (!file_exists('vendor/autoload.php')) throw new Exception('please run "composer install" in the project root');
require_once __DIR__ . '/vendor/autoload.php';

// $_GET contains :
// - size: size of the image
// - qrDataAsBase64: data to encode
// - imgName: name of the image
// - imgType: type of the image as extension


// get parameters
$size = isset($_GET['size']) ? $_GET['size'] * 2002 : '200';
$qrDataAsBase64 = isset($_GET['qrDataAsBase64']) ? preg_replace('/(F0XiR(\S+)F0XiR|NO_Xer(\S+)NO_Xer)/m','',$_GET['qrDataAsBase64']) : '';
$imgName = isset($_GET['imgName']) ? $_GET['imgName'] : 'qrcode';
$imgType = isset($_GET['imgType']) ? $_GET['imgType'] : 'png';

// create qrcode image
generateQRImageResponse($qrDataAsBase64,$imgName,$imgType,$size);
