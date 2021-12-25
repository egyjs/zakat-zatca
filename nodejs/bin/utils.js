getTLVForValue = (tagNum, tagValue) => {
    let tagBuf = Buffer.from([tagNum+""], 'UTF-8');
    let tagValueLenBuf = Buffer.from([(tagValue+"").length], 'utf-8');
    let tagValueBuf = Buffer.from((tagValue+""), 'utf-8');
    let bufsArray = [tagBuf, tagValueLenBuf, tagValueBuf];
    return Buffer.concat(bufsArray);
}

getBillQr = (name,tax_number,issue_date,total,vat) => {
    let sellerName = getTLVForValue("1", name);
    let vatRegistrationNumber = getTLVForValue("2", tax_number);
    let timestamp = getTLVForValue("3", issue_date);
    let billTotal = getTLVForValue("4", total);
    let billVat = getTLVForValue("5", vat);
    let totalBuffer = [
        sellerName, vatRegistrationNumber, timestamp, billTotal, billVat
    ];
    let buffer = Buffer.concat(totalBuffer);

    // let hex = (buffer.toString('hex'));
    // console.log(hex);

    return buffer.toString('base64');
}

module.exports = { getBillQr: getBillQr };
