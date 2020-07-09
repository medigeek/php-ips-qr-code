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

class IPSQRCodeObject {
    private string $IdentificationCode;
    private string $Version;
    private string $CharacterSet;
    private string $BankAccountNumber;
    private string $PayeeNameAndPlace;
    private string $CurrencyAndAmount;
    private string $Currency;
    private string $AmountIntegers;
    private string $AmountDecimals;
    private string $PayerAccountNumber;
    private string $PayerNameAndPlace;
    private string $PaymentCode;
    private string $PaymentPurpose;
    private string $MCC;
    private string $OneTimePaymentCode;
    private string $PayeeApprovalCodeReference;
    private string $PayeeReferenceCode;
    private string $POSTransactionReferenceCode;
    
    
    public function get(string $key, string $returntype = "array") {
        
        $tmpString = $this->$key;
        
        if ($returntype == "array") {
            return $tmpString;
        }
        elseif ($returntype == "json") {
            $jsonArray = json_encode($tmpString);
            return $jsonArray;
        }
    }

    public function validate(string $key, $value) {
        $regExpStrings = [
            ""
        ];
        return true;
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
        ksort($keyValuePairs);
        
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
    
    private array $currencyVariables = [
        "decimalPointCharacter" => ",",
        "currencyName" => "RSD",
    ];
    
    private array $variableValidationRegExpStrings = [
        //\\p{L} = unicode letter
        "IdentificationCode"            => '/^.{1,3}$/',  //PR 3a (max 3, a=alpha chars)
        "Version"                       => '/^[0-9]{1,2}$/',  //01 2n (max 2, n=numeric chars)
        "CharacterSet"                  => '/^[0-9]{1}$/',  //1 = 1n
        "BankAccountNumber"             => '/^[0-9]{18}$/',  //18n
        "PayeeNameAndPlace"             => '/^.{1,70}$/m',  //1..70a
        "CurrencyAndAmount"             => '/^[0-9,A-Z]{5,20}$/', //5..20n RSDx,xx
        "PayerAccountNumber"            => '/^[0-9]{1}$/', //18n
        "PayerNameAndPlace"             => '/^.{1,70}?$/m', //0..70a
        "PaymentCode"                   => '/^[0-9]{3}$/',  //3n -- sifra placanja npr 122
        "PaymentPurpose"                => '/^.{1,35}$/m',  //0..35a
        "MCC"                           => '/^[0-9]{4}$/',  //4n
        "OneTimePaymentCode"            => '/^[0-9]{5}$/',  //5n
        "PayeeApprovalCodeReference"    => '/^[0-9]{0,35}$/',  //0..35a Poziv na broj odobrenja primaoca placanja
        "PayeeReferenceCode"            => '/^[0-9]{0,140}$/',  //0..140a Referenca primaoca placanja
        "POSTransactionReferenceCode"   => '/^[0-9]{19}$/', //19n Referenca koja identifikuje transakciju na prodajnom mestu
        "Currency"                      => '/^[A-Z]{1,3}$/',
        "AmountInteger"                 => '/^[0-9]+$/',
        "AmountDecimals"                => '/^[0-9]+$/',
    ];

    private array $QRCodeKeyMap = [
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
    private $QRCodeObject;
    private string $QRCodeString;
    
    public function __construct($QRCodeString)
    {
        //set QRCodeObject
        $this->QRCodeObject = new IPSQRCodeObject();
        //set QRCodeString
        $this->QRCodeString = $QRCodeString;
        //parse
        $this->parse();
        var_dump($this->get());
    }
    
        
    public function mapKeys()
    {
        foreach ($this->QRCodeParsed as $keyCode => $value)
        {
            if (array_key_exists($keyCode, $this->QRCodeKeyMap)) {
                $keyName = $this->QRCodeKeyMap[$keyCode];
                //$this->QRCodeMapped[$this->QRCodeKeyMap[$keyCode]] = $value;
                $this->setQRCodeObjectVar($keyName, $value);
            }
        }
    }
    
    public function validate($keyName, $value)
    {
        $regexp = $this->variableValidationRegExpStrings[$keyName];
        if (!preg_match($regexp, $value)){
            echo("Warning: Failed validation: $keyName -- $value -- $regexp\n");
            return false;
        }
        return true;
    }
    
    public function setQRCodeObjectVar($keyName, $value) {
        if ($this->validate($keyName, $value)) {
            $this->QRCodeObject->set($keyName, $value);
        }
    }
    
    public function get(string $returntype = 'array') {
        //return QRCodeObject vars
        return $this->QRCodeObject->getAll($returntype);
    }
    
    public function parse() {
        //parse and set QRCodeParsed
        $this->parseSplit();
        //map keyCode to keyName (QRCodeObject vars)
        $this->mapKeys();
        //parse and define currency and amount properties from CurrencyAndAmount
        $this->parseCurrencyAndAmount();
    }
    
    public function parseSplit() {
        $splitQRCode = explode("|", $this->QRCodeString);
        
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
        $splitCurrency = explode(
            $this->currencyVariables["currencyName"], 
            $s
        );
        $splitAmount = explode(
            $this->currencyVariables["decimalPointCharacter"], 
            $splitCurrency[1]
        );
        
        //set QRCodeObject variables
        $this->setQRCodeObjectVar("Currency", $this->currencyVariables["currencyName"]);
        $this->setQRCodeObjectVar("AmountInteger", $splitAmount[0]);
        $this->setQRCodeObjectVar("AmountDecimals", $splitAmount[1]);
    }
    
    
}
