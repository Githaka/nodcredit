<?php

namespace App\NodCredit\Statement\PdfParser\Parsers;

use App\NodCredit\Statement\PdfParser\ParserInterface;
use App\NodCredit\Statement\Statement;
use App\NodCredit\Statement\Transaction;
use App\NodCredit\Statement\Transactions;
use Carbon\Carbon;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class GuarantyTrustBankParserThree implements ParserInterface
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
    private $dateFormat = 'd-M-y';

    /**
     * GuarantyTrustBankParserThree constructor.
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

        // Closing balance
        // Transactions
        $period = $this->parseStatementPeriod();

//        dd($this->parseTransactions());

        $statement = new Statement($this->file);
        $statement
            ->setBankName($this->bankName)
            ->setAccountNumber($this->parseAccountNumber())
            ->setCustomerName($this->parseCustomerName())
            ->setPeriod(array_get($period, 'start_at'), array_get($period, 'end_at'))

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
        $rows = $page->getTextArray();
        $rows = array_values(array_filter($rows));
        $count = count($rows);

        $title = array_get($rows,  $count- 2);
        $name = array_get($rows, $count - 1);

        if (! $name OR strtoupper($title) !== 'CUSTOMER STATEMENT') {
            throw new \Exception('Can`t find customer name');
        }

        return $name;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function parseStatementPeriod(): array
    {
        $dateFormat = 'd/M/Y';
        $page = array_get($this->pdf->getPages(), 0);

        $text = $page->getText();

        preg_match('#Period:\s([0-9]{1,2}\/[a-z]{3}\/[0-9]{4})\s+To\s+([0-9]{1,2}\/[a-z]{3}\/[0-9]{4})\n#ui', $text, $found);

        if (! array_get($found, 1) OR ! array_get($found, 2)) {
            throw new \Exception('Can`t find Statement period');
        }

        return [
            'start_at' => $this->valueToDate($found[1], $dateFormat),
            'end_at' => $this->valueToDate($found[2], $dateFormat),
        ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function parseAccountNumber(): string
    {
        $page = array_get($this->pdf->getPages(), 0);
        $text = $page->getText();

        preg_match('#Account No: ([0-9]{10,})#ui', $text, $found);

        if (! array_get($found, 1)) {
            throw new \Exception('Can`t find Account No');
        }

        return $this->cleanCell($found[1]);
    }

    /**
     * @return Transactions
     * @throws \Exception
     */
    private function parseTransactions(): Transactions
    {
        $transactions = new Transactions();

        $text = $this->pdf->getText();

        // Remove footer
        $text = preg_replace("#OR THE CUSTOMER INFORMATION UNIT.+\nPLEASE.+#ui", "", $text);

        $headers = [
            'Trans Date',
            'Reference',
            'Value Date',
            'Debit',
            'Credit',
            'Balance',
            'Remarks'
        ];
        dd($this->pdf->getPages()[0]->getText());


        // Remove headers
        $h = implode("\t", $headers) . "\t\n";

        $text = str_replace($h, '', $text);

        // Remove details text block inside transactions
        $text = preg_replace('#Currency:Naira.+CUSTOMER STATEMENT\t\n[\w\s]+\n#sium', '', $text);

        $text = preg_replace("#\t#u", "\n", $text);
        $text = preg_replace("#[\n]+#um", "\n", $text);

        $rows = explode("\n", $text);
        dd($rows);

        if (count($output) === 0) {
            throw new \Exception('Can`t find Transactions section');
        }

        if (count($output) === 1) {
            throw new \Exception('Transactions count: 0');
        }

        return $transactions;
    }

    /**
     * @param string $value
     * @param string $format
     * @return null|Carbon
     */
    private function valueToDate(string $value, string $format = '')
    {
        $format = $format ?: $this->dateFormat;

        $value = $this->cleanCell($value);
        $value = preg_replace('#\s#u', ' ', $value);

        try {
            return Carbon::createFromFormat($format, $value);
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