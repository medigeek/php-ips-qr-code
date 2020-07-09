<?php

/*
 * The MIT License
 *
 * Copyright 2020 Savvas Radevic.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MediGeek;

class IPSQRCode {
    private $QRCodeKeyMap = [
        "K"     => "IdentificationCode", //PR 3a (max 3, a=alpha chars)
        "V"     => "Version", //01 2n (max 2, n=numeric chars)
        "C"     => "CharacterSet", //1 = 1n
        "R"     => "BankAccountNumber", //18n
        "N"     => "PayeeNameAndPlace", //1..70n
        "I"     => "CurrencyAndAmount",//5..20n RSDx,xx
        "O"     => "PayerAccountNumber",//18n
        "P"     => "PayerNameAndPlace",//0..70a
        "SF"    => "PaymentCode", //3n -- sifra placanja npr 122
        "S"     => "PaymentPurpose", //0..35a
        "M"     => "MCC", //4n
        "JS"    => "OneTimePaymentCode", //5n
        "RO"    => "PayeeApprovalCodeReference", //0..35a Poziv na broj odobrenja primaoca placanja
        "RL"    => "PayeeReferenceCode", //0..140a Referenca primaoca placanja
        "RP"    => "POSTransactionReferenceCode" //19n Referenca koja identifikuje transakciju na prodajnom mestu
    ];
    
    private array $QRCodeParsed = [];
    private array $QRCodeMapped = [];
    
    private string $IdentificationCode;
    private string $Version;
    private string $CharacterSet;
    private string $BankAccountNumber;
    private string $PayeeNameAndPlace;
    private string $CurrencyAndAmount;
    private string $PayerAccountNumber;
    private string $PayerNameAndPlace;
    private string $PaymentCode;
    private string $PaymentPurpose;
    private string $MCC;
    private string $OneTimePaymentCode;
    private string $PayeeApprovalCodeReference;
    private string $PayeeReferenceCode;
    private string $POSTransactionReferenceCode;
    
    
    public function __construct(array $QRCodeParsed)
    {
        $this->QRCodeParsed = $QRCodeParsed;
        $this->mapKeys();
    }
    
    public function mapKeys()
    {
        foreach ($this->QRCodeParsed as $key => $value)
        {
            if (array_key_exists($key, $this->QRCodeKeyMap)) {
                $this->QRCodeMapped[$this->QRCodeKeyMap[$key]] = $value;
            }
        }
        
        return true;
    }
    
     
    
    public function get(string $key, string $returntype = "array") {
        
        $tmpString = $this->$key;
        
        if ($returntype == "array") {
            return $tmpString;
        }
        elseif ($returntype == "json") {
            $jsonArray = json_encode($tmpString);
        }
    }
    
    public function set(string $key, $value) {
        $this->$key = $value;
        return true;
    }
    
    public function getMultiple(array $keys, string $returntype = "array") {
        $tmpArray = [];
        foreach ($keys as $key) {
            $tmpArray[] = $this->$key;
        }
        
        if ($returntype == "array") {
            return $tmpArray;
        }
        elseif ($returntype == "json") {
            $jsonArray = json_encode($tmpArray);
            return $jsonArray;
        }
    }
    
    public function setMultiple(array $keyValuePairs) {
        foreach ($keyValuePairs as $key => $value) {
            $this->$key = $this->$value;
        }
        
    }
    
    public function getAll(string $returntype = "array") {
        $keyValuePairs = get_object_vars($this);
        
        if ($returntype == "array") {
            return $keyValuePairs;
        }
        elseif ($returntype == "json") {
            $KeyValuePairsJSON = json_encode($keyValuePairs);
            return $KeyValuePairsJSON;
        }
    }
}

/**
 * Description of IPSQRCode
 *
 * @author Savvas Radevic
 */
class IPSQRCodeParser {
    
    private $QRCodeParsed = [];
    private $QRCodeMapped = [];
    private $QRCodeObject = [];
    
    public function __construct($QRCode)
    {
        //echo $QRCode;
        $this->parseSplit($QRCode);
        //var_dump($this->QRCodeParsed);
        //$QRCodeObject = new IPSQRCode($this->QRCodeParsed);
        $this->QRCodeObject = new IPSQRCode($this->QRCodeParsed);
        
        $this->QRCodeMapped = $this->QRCodeObject->get("QRCodeMapped");
        $this->parseCurrencyAndAmount();
        var_dump($this->QRCodeMapped);
        //var_dump($this->QRCodeObject->get("QRCodeMapped"));
        //var_dump();
    }
    
    public function parseSplit($QRCode) {
        $splitQRCode = explode("|", $QRCode);
        
        foreach ($splitQRCode as $i) {
            $spliti = explode(":", $i);
            //key = get first item array
            $key = $spliti[0];
            //val = get the rest of the items
            $val = implode(",", array_slice($spliti, 1));
            $this->QRCodeParsed[$key] = $val;
        }
    }
    
    public function parseCurrencyAndAmount()
    {
        $s = $this->QRCodeParsed["I"];
        $currencyName = "RSD";
        $splitCurrency = explode($currencyName, $s);
        $decimalPointCharacter = ",";
        $splitAmount = explode($decimalPointCharacter, $splitCurrency[1]);
        
        //var_dump($splitAmount);
        $newArray = $this->QRCodeMapped;
        $newArray["CurrencyAndAmount"] = [
            "Currency" => $currencyName,
            "AmountInteger" => $splitAmount[0],
            "AmountDecimals" => $splitAmount[1]
        ];
        
        $this->QRCodeMapped = $newArray;
        $this->QRCodeObject->set("QRCodeMapped", $this->QRCodeMapped);
        
    }
    
    
}


new IPSQRCodeParser("K:PR|V:01|C:1|R:160000000003465595|N:JKP INFOSTAN TEHNOLOGIJE BEOGRAD|I:RSD9999,99|SF:122|S:OBJEDINJENA NAPLATA|RO:11800515599052-20060-1");
