<?php

namespace App\NodCredit\Statement\PdfParser\Parsers;

use App\NodCredit\Statement\PdfParser\ParserInterface;
use App\NodCredit\Statement\Statement;
use App\NodCredit\Statement\Transaction;
use App\NodCredit\Statement\Transactions;
use Carbon\Carbon;
use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class GuarantyTrustBankParserOne implements ParserInterface
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
    private $dateFormat = 'd-M-Y';
    private $columnHeaderSeparator = "\n\n\n\n";
    private $columnSeparator = "\n\n\n";
    private $footerTextPattern = '#(This is a computer generated Email\. Please address all enquiries .+\|\n\n.+ Information Unit of your local branch\.[0-9]+\.\n)#im';
    private $headers = [
        'Trans. Date',
        'Value. Date',
        'Reference',
        'Debits',
        'Credits',
        'Balance',
        'Originating Branch',
        'Remarks'
    ];

    /**
     * GuarantyTrustBankParserOne constructor.
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

        $statement = new Statement();
        $statement
            ->setBankName($this->bankName)
            ->setAccountNumber($this->parseAccountNumber())
            ->setCustomerName($this->parseCustomerName())
            ->setPeriod(array_get($period, 'start_at'), array_get($period, 'end_at'))
            ->setTransactions($this->parseTransactions())
            ->useLastTransactionAsClosingBalance()
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

        $text = $page->getText();

        preg_match('/CUSTOMER STATEMENT\n\n(.+)\n/im', $text, $found);

        if (! array_get($found, 1)) {
            throw new \Exception('Can`t find customer name');
        }

        return array_get($found, 1);
    }

    /**
     * @return Transactions
     * @throws \Exception
     */
    private function parseTransactions(): Transactions
    {
        $transactions = new Transactions();

        $text = $this->pdf->getText();

        // Remove footers on each page
        $text = preg_replace($this->footerTextPattern, "", $text);

        // Search transactions section
        preg_match("#" . implode($this->columnHeaderSeparator, $this->headers) . "(.*)-$#sm", $text, $output);

        if (count($output) === 0) {
            throw new \Exception('Can`t find Transactions section');
        }

        if (count($output) === 1) {
            throw new \Exception('Transactions count: 0');
        }

        $transactionsString = trim($output[1]);

        $cells = explode($this->columnSeparator, $transactionsString);

        $rows = [];
        $row = [];
        $columnIndex = 1;

        $cells = array_map([$this, 'cleanCell'], $cells);

        foreach ($cells as $index => $cell) {

            // Detect columns
            if ($columnIndex === 1) {

                // First two columns must be dates
                $transDate = $this->valueToDate($cell);
                $valueDate = $this->valueToDate(array_get($cells, $index+1, ''));

                // Go to next cell
                if (! $transDate OR ! $valueDate) {
                    continue;
                }

                $row['transaction_date'] = $transDate;
                $row['value_date'] = $valueDate;
            }
            else if ($columnIndex === 2) {
                $row['value_date'] = $this->valueToDate($cell);
            }
            else if ($columnIndex === 3) {
                $row['reference'] = $cell;
            }
            else if ($columnIndex === 4) {
                $row['debit'] = $this->valueToMoney($cell);
            }
            else if ($columnIndex === 5) {
                $row['credit'] = $this->valueToMoney($cell);
            }
            else if ($columnIndex === 6) {
                $row['balance'] = $this->valueToMoney($cell);
            }
            else if ($columnIndex === 7) {
                $row['originating_branch'] = $cell;
            }
            else if ($columnIndex === 8) {
                $row['remarks'] = $cell;

                // Close and reset row
                $rows[] = $row;
                $row = [];
                $columnIndex = 1;
                continue;
            }

            $columnIndex++;
        }

        foreach ($rows as $row) {
            try {
                $transactions->push($this->convertRowToTransaction($row));
            }
            catch (\Exception $exception) {
                continue;
            }
        }

        return $transactions;
    }

    /**
     * @param array $rows
     * @return string
     */
    private function rowsToHtml(array $rows = [])
    {
        $html = '<style>table {border: 1px solid; border-collapse: collapse} table td, table th {border: 1px solid; padding: 5px;}</style>';

        $html .= '<table><thead><tr><th>#</th><th>' . implode('</th><th>', $this->headers) . '</th></tr></thead>';

        foreach ($rows as $index => $row) {
            $html .= '<tr><td>' . ($index + 1) . ' </td>';

            $html .= '<td> ' . array_get($row, 'transaction_date') . '</td>';
            $html .= '<td> ' . array_get($row, 'value_date') . '</td>';
            $html .= '<td> ' . array_get($row, 'reference') . '</td>';
            $html .= '<td> ' . array_get($row, 'debit') . '</td>';
            $html .= '<td> ' . array_get($row, 'credit') . '</td>';
            $html .= '<td> ' . array_get($row, 'balance') . '</td>';
            $html .= '<td> ' . array_get($row, 'originating_branch') . '</td>';
            $html .= '<td> ' . array_get($row, 'remarks') . '</td>';

            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;

    }

    /**
     * @param array $row
     * @return Transaction
     */
    private function convertRowToTransaction(array $row): Transaction
    {
        if (array_get($row, 'debit', 0) > 0) {
            $type = Transaction::TYPE_DEBIT;
            $amount = array_get($row, 'debit');
        }
        else {
            $type = Transaction::TYPE_CREDIT;
            $amount = array_get($row, 'credit');
        }

        return new Transaction(
            $type,
            $amount,
            array_get($row, 'balance'),
            array_get($row, 'transaction_date'),
            array_get($row, 'value_date'),
            array_get($row, 'reference', ''),
            array_get($row, 'remarks', '')
        );
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function parseStatementPeriod(): array
    {
        $page = array_get($this->pdf->getPages(), 0);

        $text = $page->getText();

        preg_match('/Statement Period.+:(.+) to (.+)/i', $text, $found);

        if (! array_get($found, 1) OR ! array_get($found, 2)) {
            throw new \Exception('Can`t find Statement period');
        }

        $startDate = \Carbon\Carbon::createFromFormat($this->dateFormat, $found[1]);
        $endDate = \Carbon\Carbon::createFromFormat($this->dateFormat, $found[2]);

        return [
            'start_at' => $startDate,
            'end_at' => $endDate,
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

        preg_match('#Account No\n\n\n(.+)\n#i', $text, $found);

        if (! array_get($found, 0) OR ! array_get($found, 1)) {
            throw new \Exception('Can`t find Account No');
        }

        return $this->cleanCell($found[1]);
    }

    /**
     * @param string $value
     * @return null|Carbon
     */
    private function valueToDate(string $value)
    {
        try {
            return \Carbon\Carbon::createFromFormat($this->dateFormat, $value);
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