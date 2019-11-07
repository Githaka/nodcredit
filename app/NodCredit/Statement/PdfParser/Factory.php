<?php

namespace App\NodCredit\Statement\PdfParser;

use App\NodCredit\Statement\Exceptions\FileReaderException;
use App\NodCredit\Statement\Exceptions\RecognizingStatementException;
use App\NodCredit\Statement\PdfParser\Parsers\GuarantyTrustBankParserOne;
use App\NodCredit\Statement\PdfParser\Parsers\GuarantyTrustBankParserTwo;

class Factory
{
    private $pdf;
    private $reader;

    public function __construct(\Smalot\PdfParser\Parser $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $file
     * @return ParserInterface
     * @throws FileReaderException
     * @throws RecognizingStatementException
     */
    public static function createParserFromFile(string $file): ParserInterface
    {
        $factory = app(static::class);

        try {
            $factory->readFile($file);
        }
        catch (\Exception $exception) {
            throw new FileReaderException($exception->getMessage());
        }

        $details = $factory->pdf()->getDetails();
        $text = $factory->pdf()->getText();

        // Guaranty Trust Bank: Template #1
        if (array_get($details, 'Author') === 'Appdev-GTBank Plc') {
            return app(GuarantyTrustBankParserOne::class, ['file' => $file]);
        }
        // Guaranty Trust Bank: Template #2
        else if (array_get($details, 'Producer') === 'Select.Pdf for .NET v2018.3.0') {
            return app(GuarantyTrustBankParserTwo::class, ['file' => $file]);
        }

        throw new RecognizingStatementException();
    }

    public function readFile(string $file)
    {
        $this->pdf = $this->reader->parseFile($file);
    }

    public function pdf()
    {
        return $this->pdf;
    }

}