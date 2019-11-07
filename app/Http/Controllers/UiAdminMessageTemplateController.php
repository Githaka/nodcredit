<?php

namespace App\Http\Controllers;

use App\MessageTemplate;
use App\NodCredit\Message\Collections\TemplateCollection;
use App\NodCredit\Message\Template;
use Illuminate\Http\Request;

class UiAdminMessageTemplateController extends Controller
{
    public function getIndex()
    {
        return view('admin.message-templates.index', [
            'templates' => TemplateCollection::getAll(),
            'title' => 'Message templates'
        ]);
    }

    public function getEdit(string $id)
    {

        $template = Template::find($id);

        return view('admin.message-templates.edit', [
            'template' => $template,
            'channels' => Template::channels(),
            'title' => 'Edit template',
        ]);
    }

    public function postStore(Request $request, $id)
    {
        $data = $request->only('title', 'message', 'channel');

        $template = Template::find($id);

        $validator = $template->validate($data);

        if ($validator->fails()) {
            return back()->with('errors', $validator->errors()->toArray());
        }

        try {
            $template->update($data);
        }
        catch (\Exception $exception) {
            return back()->with('errors', ['error' => ['Saving error']]);
        }

        return back()->with('success', 'Template saved');
    }
}
