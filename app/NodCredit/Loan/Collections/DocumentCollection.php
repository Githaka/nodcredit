<?php

namespace App\NodCredit\Loan\Collections;

use App\LoanDocument as Model;
use App\LoanDocumentType;
use App\NodCredit\Helpers\BaseCollection;
use App\NodCredit\Loan\Document;
use App\NodCredit\Loan\Exceptions\DocumentTypeException;
use Illuminate\Database\Eloquent\Collection;

class DocumentCollection extends BaseCollection
{

    /**
     * @return DocumentCollection
     * @throws \Exception
     */
    public static function findBankStatementForUnlocking(): self
    {
        $bankStatementType = LoanDocumentType::where('name', 'Bank Statement')->first();

        if (! $bankStatementType) {
            throw new DocumentTypeException('Can`t find bank statement document type');
        }

        $models = Model::where('document_type', $bankStatementType->id)
            ->where('is_unlocked', false)
            ->whereNotNull('unlock_password')
            ->where('unlock_attempts', '<', 5)
            ->get()
        ;

        return static::makeCollectionFromModels($models);
    }


    /**
     * @return DocumentCollection
     * @throws \Exception
     */
    public static function findBankStatementsForExportingToParser(): self
    {
        $bankStatementType = LoanDocumentType::where('name', 'Bank Statement')->first();

        if (! $bankStatementType) {
            throw new DocumentTypeException('Can`t find bank statement document type');
        }

        $models = Model::where('parser_status', Document::PARSER_STATUS_NEW)
            ->where('document_type', $bankStatementType->id)
            ->where('is_unlocked', true)
            ->get()
        ;

        return static::makeCollectionFromModels($models);
    }

    /**
     * @return DocumentCollection
     * @throws \Exception
     */
    public static function findBankStatementsForImportingFromParser(): self
    {
        $collection = new static();

        $bankStatementType = LoanDocumentType::where('name', 'Bank Statement')->first();

        if (! $bankStatementType) {
            throw new DocumentTypeException('Can`t find bank statement document type');
        }

        $models = Model::where('parser_status', Document::PARSER_STATUS_SENT)
            ->where('document_type', $bankStatementType->id)
            ->get();

        foreach ($models as $model) {
            $collection->push(new Document($model));
        }

        return $collection;
    }

    public static function makeCollectionFromModels(Collection $models): self
    {
        $collection = new static();

        foreach ($models as $model) {
            $collection->push(new Document($model));
        }

        return $collection;
    }

    public function push(Document $document): self
    {
        $this->items->push($document);

        return $this;
    }
}