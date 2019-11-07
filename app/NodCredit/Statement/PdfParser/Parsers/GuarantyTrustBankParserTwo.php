<?php

namespace App\NodCredit\Statement\PdfParser\Parsers;

use App\NodCredit\Statement\PdfParser\ParserInterface;
use App\NodCredit\Statement\Statement;
use App\NodCredit\Statement\Transaction;
use App\NodCredit\Statement\Transactions;
use Carbon\Carbon;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class GuarantyTrustBankParserTwo implements ParserInterface
{
    /**
     * @var Parser
     */
    private $reader;

    /**
     * @var Document
     */
    private $pdf;

    private $file;

    private $bankName = 'GUARANTY TRUST BANK';
    private $dateFormat = 'F d, Y';

    /**
     * GuarantyTrustBankParserTwo constructor.
     * @param Parser $reader
     * @param string $file
     */
    public function __construct(Parser $reader, string $file = '')
    {
        $this->reader = $reader;
        $this->file = $file;
    }

    /**
     * @param string $file
     * @return Statement
     * @throws \Exception
     */
    public function parse(string $file = ''): Statement
    {
        if ($file) {
            $this->file = $file;
        }

        if (! is_file($this->file)) {
            throw new \Exception("File $this->file not found");
        }

        try {
            $this->pdf = $this->reader->parseFile($this->file);
        }
        catch (\Exception $exception) {
            throw new \Exception("PDF Reader can`t read file $this->file");
        }

        $period = $this->parseStatementPeriod();

        $statement = new Statement($this->file);
        $statement
            ->setBankName($this->bankName)
            ->setAccountNumber($this->parseAccountNumber())
            ->setCustomerName($this->parseCustomerName())
            ->setPeriod(array_get($period, 'start_at'), array_get($period, 'end_at'))
            ->setClosingBalance($this->parseClosingBalance())
        ;

        return $statement;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function parseCustomerName(): string
    {
        $page = array_get($this->pdf->getPages(), 0);
        $text = $page->getTextArray();
        $textRows = explode("\n", array_get($text, 1, []));

        $nameRow = $textRows[0];

        if (strstr($nameRow, 'Customer') === FALSE) {
            throw new \Exception('Can`t find customer name');
        }

        $name = explode('Customer', $nameRow);
        $name = trim($name[0]);
        $name = str_replace(',', ' ', $name);
        $name = preg_replace('#[\s]+#u', ' ', $name);

        return $name;
    }

    private function parseClosingBalance(): float
    {
        $page = array_get($this->pdf->getPages(), 0);
        $text = $page->getTextArray();
        $textRows = explode("\n", array_get($text, 1, []));

        $closingBalanceRow = $textRows[8];

        preg_match('#[0-9\,\.]+#u', $closingBalanceRow, $found);

        if (! array_get($found, 0)) {
            throw new \Exception('Can`t find closing balance');
        }

        return $this->valueToMoney($found[0]);
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function parseStatementPeriod(): array
    {
        $page = array_get($this->pdf->getPages(), 0);
        $text = $page->getTextArray();
        $textRows = explode("\n", array_get($text, 1, ''));
        $periodRow = array_get($textRows, 3, '');

        preg_match_all('/([a-z]+\s[0-9]{1,2},\s[0-9]{4})/ui', $periodRow, $found);

        $dates = array_get($found, 1);

        if (! $dates) {
            throw new \Exception('Can`t find Statement period');
        }

        return [
            'start_at' => $this->valueToDate($dates[0]),
            'end_at' => $this->valueToDate($dates[1]),
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function parseAccountNumber(): string
    {
        $page = array_get($this->pdf->getPages(), 0);
        $text = $page->getTextArray();
        $textRows = explode("\n", array_get($text, 1, []));

        $accountRow = $textRows[2];

        preg_match('#^[0-9]{10,}#u', $accountRow, $found);

        if (! array_get($found, 0)) {
            throw new \Exception('Can`t find Account No');
        }

        return $this->cleanCell($found[0]);
    }

    /**
     * @param string $value
     * @return null|Carbon
     */
    private function valueToDate(string $value)
    {
        $value = $this->cleanCell($value);
        $value = preg_replace('#\s#u', ' ', $value);

        try {
            return Carbon::createFromFormat($this->dateFormat, $value);
        }
        catch (\Exception $exception) {
            return null;
        }
    }

    /**
     * @param string $value
     * @return float|null
     */
    private function valueToMoney(string $value)
    {
        $value = $this->cleanCell($value);

        if ($value === '') {
            return null;
        }

        return floatval(str_replace(',', '', $value));
    }

    /**
     * @param string $value
     * @return string
     */
    private function cleanCell(string $value): string
    {
        return trim($value);
    }
}