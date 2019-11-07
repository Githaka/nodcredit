<?php
namespace App\NodCredit\Loan;

use App\LoanDocument as Model;
use App\NodCredit\Loan\Document\ExtractParsedData;
use App\NodCredit\Loan\Document\ParsedDataToStatement;
use App\NodCredit\Loan\Document\PdfUnlocker;
use App\NodCredit\Loan\Exceptions\BankStatementException;
use App\NodCredit\Loan\Exceptions\DocumentUnlockException;
use App\User;
use Docparser\Docparser;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;

class Document
{

    const PARSER_STATUS_NEW = 'new';
    const PARSER_STATUS_SENT = 'sent';
    const PARSER_STATUS_HANDLED = 'handled';

    /**
     * @var Model
     */
    private $model;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Docparser
     */
    private $docparser;


    public static function find(string $id): self
    {
        $model = Model::findOrFail($id);

        return new static($model);
    }

    public static function storeAndCreate(File $file, array $data)
    {
        if (! $file->isFile()) {
            throw new \Exception('Param is not a file.');
        }

        $path = Storage::disk('documents')->put(array_get($data, 'loan_application_id'), $file);

        $data['path'] = $path;

        return static::create($data);

    }

    public static function create(array $data): self
    {
        $model = Model::create([
            'loan_application_id' => array_get($data, 'loan_application_id'),
            'path' => array_get($data, 'path'),
            'document_type' => array_get($data, 'document_type'),
            'document_extension' => array_get($data, 'document_extension'),
            'description' => array_get($data, 'description'),
        ]);

        return new static($model);
    }

    public function __construct(Model $model)
    {
        $this->model = $model;
    }


    public function getId(): string
    {
        return $this->model->id;
    }

    public function getDescription()
    {
        return $this->model->description;
    }

    public function getParserStatus()
    {
        return $this->model->parser_status;
    }

    public function getFullpath(): string
    {
        return $this->model->getFullpath();
    }

    public function getFilename(): string
    {
        return basename($this->getFullpath());
    }

    public function getDirectory(): string
    {
        return dirname($this->getFullpath());
    }

    public function getDocumentExtension()
    {
        return $this->model->document_extension;
    }

    public function getApplication()
    {
        if (! $this->application) {
            $this->application = Application::find($this->model->loan_application_id);
        }

        return $this->application;
    }

    public function getUser()
    {
        if (! $this->user) {
            $this->user = $this->getApplication()->getUser();
        }

        return $this->user;
    }

    public function getParsedData()
    {
        return $this->model->parsed_data;
    }

    public function getParsedDataAsObject()
    {
        return json_decode($this->getParsedData());
    }

    public function hasParsedData(): bool
    {
        return !! $this->getParsedData();
    }

    public function getParserPayload()
    {
        return $this->model->parser_payload;
    }

    public function hasBeenSentToParser(): bool
    {
        return !!$this->model->parser_sent_at;
    }

    public function isSentToParser(): bool
    {
        return $this->getParserStatus() === static::PARSER_STATUS_SENT;
    }

    public function isHandledByParser(): bool
    {
        return $this->getParserStatus() === static::PARSER_STATUS_HANDLED;
    }

    public function delete()
    {
        return $this->model->delete();
    }

    /**
     * Set parser status to new
     * @return bool
     */
    public function parserStatusNew(): bool
    {
        $this->model->parser_status = static::PARSER_STATUS_NEW;

        return $this->model->save();
    }

    /**
     * @param string|null $parser
     * @param string|null $externalId
     * @return bool
     */
    public function parserStatusSent(string $parser = null, string $externalId = null): bool
    {
        $this->model->parser = $parser;
        $this->model->parser_external_id = $externalId;
        $this->model->parser_status = static::PARSER_STATUS_SENT;
        $this->model->parser_sent_at = now();

        return $this->model->save();
    }


    public function parserStatusHandled(array $data): bool
    {
        $this->model->parser_status = static::PARSER_STATUS_HANDLED;
        $this->model->parser_payload = json_encode($data);
        $this->model->parsed_data = json_encode($this->extractParsedData($data));

        return $this->model->save();
    }

    public function getParserExternalId()
    {
        return $this->model->parser_external_id;
    }

    public function convertParsedDataToStatement()
    {
        return ParsedDataToStatement::convert($this);
    }

    /**
     * @return bool
     * @throws BankStatementException
     */
    public function exportBankStatementToParser()
    {
        if (! $this->isBankStatement()) {
            throw new BankStatementException("Document {$this->getDescription()} is not a Bank Statement.");
        }

        try {
            $parserId = $this->detectParserId();
        }
        catch (BankStatementException $exception) {
            throw $exception;
        }

        try {
            $response = $this->getDocparser()->uploadDocumentByContents(
                $parserId,
                file_get_contents($this->getFullpath()),
                $this->getId(),
                $this->getDescription() . '-' . $this->getId()
            );
        }
        catch (\Exception $exception) {
            throw new BankStatementException('Error while exporting bank statement to Docparser API. Message: ' . $exception->getMessage());
        }

        return $this->parserStatusSent($parserId, array_get($response, 'id'));
    }

    /**
     * @return bool
     * @throws BankStatementException
     */
    public function importBankStatementFromParser()
    {
        if (! $this->isBankStatement()) {
            throw new BankStatementException("Document {$this->getDescription()} is not a Bank Statement.");
        }

        try {
            $parserId = $this->detectParserId();
        }
        catch (BankStatementException $exception) {
            throw $exception;
        }

        try {
            $response = $this->getDocparser()->getResultsByDocument($parserId, $this->getParserExternalId());
        }
        catch (\Exception $exception) {
            throw new BankStatementException('Error while importing bank statement parser result. Message: ' . $exception->getMessage());
        }

        $this->parserStatusHandled($response[0]);

        return true;
    }

    public function hasParserId(): bool
    {
        try {
            if ($parserId = $this->detectParserId()) {
                return true;
            }
        }
        catch (\Exception $exception) {}

        return false;
    }

    /**
     * @return null|string
     */
    public function getUnlockPassword()
    {
        return $this->model->unlock_password;
    }

    public function getUnlockAttempts(): int
    {
        return (int) $this->model->unlock_attempts;
    }
    
    public function isUnlocked(): bool
    {
        return !! $this->model->is_unlocked;
    }

    public function increaseUnlockAttempts(): self
    {
        $this->model->unlock_attempts += 1;
        $this->model->save();

        return $this;
    }

    public function unlocked(string $message = ''): self
    {
        $this->model->is_unlocked = true;
        $this->model->unlock_response = $message;
        $this->model->save();

        return $this;
    }

    /**
     * @throws DocumentUnlockException
     * @return bool
     */
    public function unlock(): bool
    {
        return PdfUnlocker::unlock($this);
    }

    public function failedToUnlock(string $message = ''): self
    {
        $this->model->is_unlocked = false;
        $this->model->unlock_response = $message;
        $this->model->save();

        return $this;
    }

    private function isBankStatement(): bool
    {
        return strtoupper($this->getDescription()) === 'BANK STATEMENT';
    }

    private function detectParserId()
    {
        $bank = $this->getUser()->bank;

        $apiParsers = config('docparser.parsers', []);

        if ($parserId = array_get($apiParsers, $bank->id)) {
            return $parserId;
        }

        throw new BankStatementException("Docparser config: Parser ID not found for parsing $bank->name statements");
    }

    /**
     * @param array $data
     * @return array|null
     */
    private function extractParsedData(array $data)
    {
        return ExtractParsedData::extract($data);
    }

    private function getDocparser()
    {
        if (! $this->docparser) {
            $this->docparser = app(Docparser::class);
        }
        return $this->docparser;
    }
}