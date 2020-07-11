<?php declare(strict_types=1);

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
    private string $PayeeApprovalReferenceCode;
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
 * IPS QR Code Parser
 * 
 * As per the National Bank of Serbia specifications:
 * Odluka o opštim pravilima za izvršavanje instant transfera odobrenja 
 * „Službeni glasnik RS“, br. 65/2018, 78/2018 i 20/2019
 * Prilog 1
 * 
 * https://www.nbs.rs/internet/latinica/20/index_plp.html
 * https://www.nbs.rs/internet/latinica/20/plp/instant_odobrenja_p.pdf
 * https://www.nbs.rs/export/sites/default/internet/latinica/20/plp/instant_transfer_2019_p_p.docx
 *
 * @author Savvas Radevic
 */
class IPSQRCodeParser {
    
    private array $currencyVariables = [
        "decimalPointCharacter" => ",",
        "currencyName" => "RSD",
    ];
    
    private array $variableValidationRegExpStrings = [
        
        /* 
         * IdentificationCode
         * Таг K: идентификациони кôд означава садржај IPS QR кôда и може имати 
         * следеће вредности: 
         *   – PR – за генерисање IPS QR кôда на штампаном рачуну-фактури примаоца плаћања;
         *   – PT – за генерисање IPS QR кôда на продајном месту примаоца плаћања, 
         * презентованог од стране примаоца плаћања (трговца);
         *   – PK – за генерисање IPS QR кôда на продајном месту примаоца плаћања, 
         * презентованог од стране платиоца (купца);
         *   – EK –  за генерисање IPS QR кôда у апликативном софтверу интернет продајног места.
         * 
         * PR|PT|PK|EK 3a (max a, a=alpha/text chars)
         */
        "IdentificationCode" => '/^(PR|PT|PK|EK)$/',
        
        /*
         * Version
         * Taг V: верзија означава верзију презентације IPS QR кôда, 
         * фиксна вредност 01.
         * 01 2n (max 2, n=numeric chars)
         */
        "Version" => '/^[0-9]{1,2}$/',
        
        /*
         * CharacterSet
         * Таг C: знаковни скуп означава знаковни скуп који се користи у 
         * презентацији, фиксна вредност 1 означава употребу UTF-8 кодног распореда.
         * 
         * 1 1n
         */
        "CharacterSet" => '/^1$/',
        
        /*
         * BankAccountNumber
         * Таг R: број рачуна примаоца плаћања означава број текућег, односно
         * другог платног рачуна примаоца плаћања у складу с прописима, који 
         * се уписује искључиво као низ од 18 цифара.
         * 
         * 18n
         */
        "BankAccountNumber" => '/^[0-9]{18}$/',
        
        /*
         * PayeeNameAndPlace
         * Таг N: назив и седиште примаоца плаћања означава пословно име или 
         * скраћено пословно име примаоца плаћања, односно његов назив под 
         * којим је евидентиран у регистру код надлежног органа. 
         * Подаци, опционо, могу обухватити и адресу седишта примаоца плаћања. 
         * Подаци се наводе редом (назив и седиште примаоца плаћања, адреса 
         * седишта), одвојени знаком за нову линију.
         * Код употребе на продајним местима трговаца користи се, ако је 
         * могуће, само скраћено пословно име. Уколико назив примаоца плаћања 
         * садржи и место седишта тог примаоца, није потребно посебно наводити 
         * седиште.
         * 
         * 1..70an
         */
        "PayeeNameAndPlace" => '/^.{1,70}$/m',
        
        /*
         * CurrencyAndAmount
         * Таг I: валута и износ новчаних средстава представља ознаку РСД и износ 
         * за који се обавезно уписује децимални зарез иза кога се не морају 
         * писати неважеће децималне нуле (нпр.  RSD1025,). Минимални износ налога 
         * је RSD0,01, а максимални износ налога је RSD999999999999,99. При уносу 
         * износа не уносе се сепаратори хиљада (.). Није дозвољено изоставити 
         * цифру за цело место у износу. На пример: RSD,01 није исправан износ, 
         * исправно је RSD0,01. Код употребе IPS QR кôда на штампаним 
         * рачунима-фактурама, прималац плаћања може у том кôду приказати и 
         * износ 0,00 динара (нпр. RSD0,00 или RSD0,).
         * 
         * 5..18an RSDx,xx
         */
        "CurrencyAndAmount" => '/^RSD[0-9]{1,12},[0-9]{0,2}$/',
        
        /*
         * PayerAccountNumber
         * Таг O: број рачуна платиоца означава број текућег, односно другог 
         * платног рачуна платиоца у складу с прописима, који се уписује 
         * искључиво као низ од 18 цифара.
         * 
         * 18n
         */
        "PayerAccountNumber" => '/^[0-9]{18}$/', 
        
        /*
         * Таг P: назив и седиште платиоца представља име и презиме, односно 
         * назив и седиште платиоца, адресу, односно седиште платиоца (адреса 
         * седишта платиоца) који се наводе тим редом, одвојени знаком за нову 
         * линију.
         * 
         * 0..70an
         */
        "PayerNameAndPlace" => '/^.{0,70}?$/m',
        
        /*
         * PaymentCode
         * Таг SF: шифра плаћања означава нумерички податак од три цифре, од 
         * којих прва идентификује облик плаћања, а друге две основ плаћања.
         * 
         * 3n
         */
        "PaymentCode" => '/^[0-9]{3}$/',
        
        /*
         * PaymentPurpose
         * Таг S: сврха плаћања означава податке о намени и основу преноса 
         * новчаних средстава.
         * 
         * 0..35an
         */
        "PaymentPurpose" => '/^.{1,35}$/m',
        
        /*
         * MCC
         * Таг M: MCC је ознака-кôд категорије трговца (енг. Merchant Code 
         * Category) у складу са ISO 18245. Списак дозвољених кôдова утврђује 
         * се техничком документацијом платног система у којем се извршава 
         * инстант трансфер одобрења на основу употребе платног инструмента на 
         * продајном месту у којој је дато и упутство за пресликавање тих 
         * кôдова у налог за пренос у том систему.
         * 
         * 4n
         */
        "MCC" => '/^[0-9]{4}$/',
        
        /*
         * OneTimePaymentCode
         * Таг JS: једнократна шифра платиоца представља ТОТР вредност 
         * (енг. Time-based One Time Password) – једнократну шифру чије је 
         * важење временски ограничено, нпр. пет минута. Једнократну шифру 
         * генерише платиочев пружалац платних услуга, која се користи ради 
         * одобравања извршења платне трансакције, у складу с оквирним уговором 
         * и прописима.
         * 
         * 10an
         */
        "OneTimePaymentCode"            => '/^[0-9]{5}$/',
        
        /*
         * PayerReferenceCode
         * Tаг RK: референца платиоца (купца) представља ознаку коју утврђује 
         * платиочев пружалац платних услуга и која се, као допунски податак о 
         * платиоцу, користи ради одобравања извршења платне трансакције, у 
         * складу са оквирним уговором и прописима.
         * 
         * 8an
         */
        "PayerReferenceCode"    => '/^[0-9]{0,8}$/',
        
        /*
         * PayeeApprovalReferenceCode
         * Таг RO: позив на број одобрења примаоца плаћања означава допунске 
         * податке за примаоца плаћања у складу с прописима. Позив на број 
         * одобрења обухвата и број модела који се уписује испред садржаја 
         * позива на број одобрења. 
         * Ако позив на број није утврђен ни према једном моделу (нпр. позив 
         * на број одобрења је 1234), тада се уписују две водеће нуле 
         * (нпр. 001234). 
         * Ако је позив на број утврђен према моделу 97, садржај позива на број 
         * уписује се искључиво у низу без цртица, односно размака (нпр. позив 
         * на број одобрења 9714123412, у којем је 97 број модела, а 14 је 
         * контролни број који је у складу с прописима израчунат по моделу 97 
         * за низ 123412).
         * У случају генерисања IPS QR кôда са идентификационим кôдом 
         * „PT“ и „EK“ – пружалац платних услуга трговца (прихватилац) може 
         * користити овај таг за генерисање референце која идентификује 
         * трансакцију на продајном месту и која представља јединствени 
         * идентификатор самог плаћања, узимајући у обзир и потребу за правилном 
         * идентификацијом наплатног места трговца на којем се може издати 
         * захтев за плаћање, ради недвосмислене идентификације тог наплатног 
         * места у извршавању платне трансакције.
         * 
         * 0..35an Poziv na broj / Referenca primaoca placanja
         */
        "PayeeApprovalReferenceCode" => '/^[0-9]{0,35}$/',
        
        /*
         * PayeeReferenceCode
         * Таг RL: референца примаоца плаћања означава допунске податке за 
         * платиоца/примаоца плаћања у слободној форми.
         * 
         * 0..140an Referenca koja identifikuje transakciju na prodajnom mestu
         */
        "PayeeReferenceCode" => '/^[0-9]{0,140}$/',
        
        /*
         * POSTransactionReferenceCode
         * Таг RP: референца која идентификује трансакцију на продајном месту 
         * представља јединствени идентификатор самог плаћања, укупне дужине 
         * 19 карактера у следећем формату: 
         * 
         * [TID 8an][Year 2n][Julian day – redni broj dana u godini 3n][Transaction number 6n] 
         * 
         * Сваки део наведене референце мора имати утврђен број карактера, тако 
         * да се обавезно уписују и водеће нуле. 
         * TID (енг. Тerminal Identification) представља јединствену 
         * идентификациону ознаку коју дефинише пружалац платних услуга трговца 
         * (прихватилац) за свако наплатно место трговца на којем се може 
         * издати захтев за плаћање, ради недвосмислене идентификације тог 
         * наплатног места у извршавању платне трансакције.
         * 
         * 19an
         */
        "POSTransactionReferenceCode"   => '/^[0-9]{19}$/',
        
        "Currency"                      => '/^RSD$/',
        "AmountInteger"                 => '/^[0-9]{1,12}$/',
        "AmountDecimals"                => '/^[0-9]{0,2}$/',
    ];

    private array $QRCodeKeyMap = [
        "K"     => "IdentificationCode",
        "V"     => "Version",
        "C"     => "CharacterSet",
        "R"     => "BankAccountNumber",
        "N"     => "PayeeNameAndPlace",
        "I"     => "CurrencyAndAmount",
        "O"     => "PayerAccountNumber",
        "P"     => "PayerNameAndPlace",
        "SF"    => "PaymentCode",
        "S"     => "PaymentPurpose",
        "M"     => "MCC",
        "JS"    => "OneTimePaymentCode",
        "RO"    => "PayeeApprovalReferenceCode",
        "RL"    => "PayeeReferenceCode",
        "RP"    => "POSTransactionReferenceCode"
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
