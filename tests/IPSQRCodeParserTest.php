<?php

namespace MediGeek;

//require 'bootstrap.php';

use PHPUnit\Framework\TestCase;
use MediGeek\IPSQRCodeParser;

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

/**
 * IPSQRCodeTest
 *
 * @author Savvas Radevic
 */
class IPSQRCodeParserTest extends TestCase {

    public function testIPSQRCodeParser(): void {
        $c = new IPSQRCodeParser("K:PR|V:01|C:1|R:160000000003465595|N:JKP INFOSTAN TEHNOLOGIJE BEOGRAD|I:RSD9999,99|SF:122|S:OBJEDINJENA NAPLATA|RO:11800515599052-20060-1");
        
        $this->assertEquals(
            
            array(
                "K" => "PR",
                "V" => "01",
                "C" => "1",
                "R" => "160000000003465595",
                "N" =>"JKP INFOSTAN TEHNOLOGIJE BEOGRAD",
                "I" => "RSD9999,99",
                "SF" => "122",
                "S" => "OBJEDINJENA NAPLATA",
                "RO"=> "11800515599052-20060-1",
            ),
            
            $c->parseSplit()
        );
    }

}
