<?php
namespace App\NodCredit\Loan\Document;

use App\NodCredit\Loan\Document;
use App\NodCredit\Loan\Exceptions\DocumentUnlockException;
use Symfony\Component\Process\Process;

class PdfUnlocker
{
    /**
     * @param Document $document
     * @return bool
     * @throws DocumentUnlockException
     */
    public static function unlock(Document $document): bool
    {
        if (! strtolower($document->getDocumentExtension()) === 'pdf') {
            throw new DocumentUnlockException("Document extension is [{$document->getDocumentExtension()}], but it must be [pdf]");
        }

        if ($document->isUnlocked()) {
            return true;
        }

        if (! $document->getUnlockPassword()) {
            throw new DocumentUnlockException('Document has not an unlock password');
        }

        $document->increaseUnlockAttempts();

        $process = new Process([
            config('qpdf.bin_path'),
            '--decrypt',
            "--password={$document->getUnlockPassword()}",
            '--replace-input',
            $document->getFilename(),
        ], $document->getDirectory());

        try {
            $process->run();
        }
        catch (\Exception $exception) {
            $document->failedToUnlock($process->getErrorOutput());

            throw new DocumentUnlockException($exception->getMessage());
        }

        // Error while unlocking
        if ($process->getErrorOutput()) {
            $document->failedToUnlock($process->getErrorOutput());

            throw new DocumentUnlockException($process->getErrorOutput());
        }

        $document->unlocked('Unlocked');

        return true;
    }
}