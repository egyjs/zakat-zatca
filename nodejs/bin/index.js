#! /usr/bin/env node
const utils = require('./utils.js')
const yargs = require("yargs");
const {getBillQr} = require("./utils");
const usage = "\nUsage: zatca_sa_qrcode <sellerName> <vatRegistrationNumber> <timestamp> <totalWithVat> <billVat>";
const options = yargs.usage(usage)
    .option('sellerName', {
        alias: 'name',
        describe: 'Seller name',
        type: 'string',
        demandOption: true
    })
    .option('vatRegistrationNumber', {
        alias: 'rn',
        describe: 'Vat registration number',
        type: 'string',
        demandOption: true
    })
    .option('timestamp', {
        alias: 'time',
        describe: 'Timestamp',
        type: 'string',
        demandOption: true
    })
    .option('totalWithVat', {
        alias: 'total',
        describe: 'Bill total with vat',
        type: 'string',
        demandOption: true
    })
    .option('vat', {
        // alias: 'vat',
        describe: 'Bill vat',
        type: 'string',
        demandOption: true
    })
    // .option("l", {alias:"languages", describe: "List all supported languages.", type: "boolean", demandOption: false })
    .help(true)
    .argv;


console.log(getBillQr(options.sellerName, options.vatRegistrationNumber, options.timestamp, options.totalWithVat, options.vat));
