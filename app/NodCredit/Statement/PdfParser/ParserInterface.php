<?php

namespace App\NodCredit\Statement\PdfParser;


use App\NodCredit\Statement\Statement;

interface ParserInterface
{

    public function parse(string $file = ''): Statement;

}