<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleRequiredDocumentUploaded
{

    public function handle($event)
    {
        $event->application->required_documents_uploaded = true;
        $event->application->save();
    }
}
