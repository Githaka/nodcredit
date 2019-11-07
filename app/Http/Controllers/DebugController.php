<?php

namespace App\Http\Controllers;


use App\LoanApplication;
use App\Mail\LoanApplicationNewAmountConfirmMail;
use App\Mail\LoanApplicationProcessingMail;
use App\Mail\LoanApplicationRejectedMail;
use App\Mail\LoanApplicationValidatorReportMail;
use App\Mail\LoanStatementPeriodNotValidMail;
use App\NodCredit\Loan\Application;
use App\NodCredit\Loan\Application\Automation;
use App\NodCredit\Loan\Application\Validator;
use App\NodCredit\Loan\Application\ValidatorResult;
use App\NodCredit\Loan\Collections\ApplicationCollection;
use App\NodCredit\Settings;
use App\UserCard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Smalot\PdfParser\Parser;

class DebugController extends Controller
{

    public function debug()
    {
        $signatures = DB::table('user_cards')
            ->select([
                'signature',
                DB::raw('COUNT(`signature`) as `count`')
            ])
            ->groupBy('signature')
            ->having('count', '>', 1);

        $signatures = $signatures->get()->pluck('signature')->toArray();

        $result = [];

        $cards = UserCard::whereIn('signature', $signatures)->withTrashed()->get();

        foreach ($cards as $card) {

            $record = array_get($result, $card->signature, [
                'users' => [],
                'cards' => [],
                'deleted' => [],
            ]);

            $record['users'][$card->user->id] = $card->user->name;
            $record['cards'][] = $card->id;

            if ($card->deleted_at) {
                $record['deleted'][] = $card->id;
            }

            $result[$card->signature] = $record;
        }

        $html = '<table border="1"><thead><tr><th>Signature</th><th>Cards</th><th>Users ID</th></tr></thead>';

        foreach ($result as $signature => $record) {
            $html .= '<tr>';

            $html .= "<td>" . $signature . "</td>";

            $cardsCell = '';

            foreach ($record['cards'] as $cardId) {
                $cardsCell .= $cardId;

                if (in_array($cardId, $record['deleted'])) {
                    $cardsCell .= ': DELETED';
                }

                $cardsCell .= '<br/>';
            }

            $html .= "<td>" . $cardsCell . "</td>";
            $html .= "<td>" . implode('<br/>', $record['users']) . "</td>";

            $html .= '</tr>';
        }

        $html .= '</table>';

        echo $html;
    }

    public function parseGTB1()
    {
        $file1 = base_path('automation/statements/guaranty-trust-bank/1/4MSn7IivUNnQgu8Vt5ZxNjTn8ENQpszbfdCBhn3e.pdf');

        $parser = app(\App\NodCredit\Statement\PdfParser\Parsers\GuarantyTrustBankParserOne::class, ['file' => $file1]);

        $statement = $parser->parse();

        dd($statement);

    }

    public function parseGTB2()
    {
        $statements = [];
        $dir = base_path('automation/statements/guaranty-trust-bank/2');
        $files = scandir($dir);

        foreach ($files as $file) {

            $path = $dir  . DIRECTORY_SEPARATOR . $file;

            if (is_file($path)) {
                $parser = app(\App\NodCredit\Statement\PdfParser\Parsers\GuarantyTrustBankParserTwo::class, ['file' => $path]);

                $statements[] = $parser->parse();
            }
        }

        dd($statements);
    }

    public function parseGTB3()
    {
        $statements = [];
        $dir = base_path('automation/statements/guaranty-trust-bank/3');
        $files = scandir($dir);

        foreach ($files as $file) {

            $path = $dir  . DIRECTORY_SEPARATOR . $file;

            if (is_file($path)) {
                $reader = new \Smalot\PdfParser\Parser();

                try {
                    $pdf = $reader->parseFile($path);
                    $statements[] = $pdf->getDetails();
                }
                catch (\Exception $exception) {
                    $statements[$file] = $exception->getMessage();
                }
            }
        }

        dd($statements);
    }

    public function parseGTB4()
    {
        $statements = [];
        $dir = base_path('automation/statements/guaranty-trust-bank/4');
        $files = scandir($dir);

        foreach ($files as $file) {

            $path = $dir  . DIRECTORY_SEPARATOR . $file;

            if (is_file($path)) {
                $reader = new \Smalot\PdfParser\Parser();

                try {
                    $pdf = $reader->parseFile($path);
                    $statements[] = $pdf->getDetails();
                }
                catch (\Exception $exception) {
                    $statements[$file] = $exception->getMessage();
                }
            }
        }

        dd($statements);
    }

    public function parseGTB5()
    {
        $statements = [];
        $dir = base_path('automation/statements/guaranty-trust-bank/5');
        $files = scandir($dir);

        foreach ($files as $file) {

            $path = $dir  . DIRECTORY_SEPARATOR . $file;

            if (is_file($path)) {

                $reader = new Parser();
                $pdf = $reader->parseFile($path);
//                $statements[] = $pdf->getPages()[0]->getText();
//                $statements[] = $pdf->getPages()[1]->getText();
                $parser = app(\App\NodCredit\Statement\PdfParser\Parsers\GuarantyTrustBankParserThree::class, ['file' => $path]);
                $statements[] = $parser->parse();
            }
        }

        dd($statements);
    }

    public function parseAccess1()
    {
        $statements = [];
        $dir = base_path('automation/statements/access-bank/3');
        $files = scandir($dir);

        foreach ($files as $file) {

            $path = $dir  . DIRECTORY_SEPARATOR . $file;

            if (is_file($path)) {

                $reader = new Parser();
                $pdf = $reader->parseFile($path);
                $statements[] = $pdf->getPages()[0]->getText();
            }
        }

        dd($statements);
    }

    public function approval()
    {

        $applications = ApplicationCollection::findReadyForPayOut();

        foreach ($applications->all() as $application) {
            Automation::handle($application);
        }

        dd($applications);
    }

    public function newAndReady()
    {
        $applications = ApplicationCollection::findNewAndReady();

        foreach ($applications->all() as $application) {
            Automation::handle($application);

        }

        dd($applications);
    }
}

