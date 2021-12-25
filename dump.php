<?php
// tutorial to how use this api
// Title: 1. First step
// Description: you need to create a new request to with your bill information
// as [ "Name" as the seller's name, "vatRegistrationNumber" or "rn" that registered, "timestamp" or "time" that the bill was created on,
// "totalWithVat" or "total" is the total amount of the bill with VAT/TAX, "vat" is the VAT/TAX amount, "size" as the QrCode image size]
// the response will be a json object with the following keys:
// "status" as the status of the response, "data" as the data of the response, or "errors" as the error of the response
// "status" can be "true" or "false"
// "data" will contain the following keys:
// ["qr_code_png","qr_code_svg","qr_code_base64"]
// you can choose what ever you want

// Title: 2. Final step
// Description: after you get the data from the response, you can use any of the three methods to get the qr code
// you can use the "qr_code_png" method to get the qr code as a png image
// you can use the "qr_code_svg" method to get the qr code as a svg image
// you can use the "qr_code_base64" method to get the qr code as a base64 string as plain text


