<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index()
    {
        $newsletters = Newsletter::orderBy('created_at', 'desc')->get();

        return view('admin.newsletters.index', compact('newsletters'));
    }

    public function create()
    {
        $newsletter = new Newsletter();

        return view('admin.newsletters.create', [
            'newsletter' => $newsletter,
            'emailTemplate' => $this->emailTemplate(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $newsletter = Newsletter::create($data);

        return redirect('/admin/newsletters/' . $newsletter->newsletterID . '/edit')
            ->with('success', 'Newsletter draft saved.');
    }

    public function edit($id)
    {
        $newsletter = Newsletter::findOrFail($id);

        return view('admin.newsletters.edit', [
            'newsletter' => $newsletter,
            'emailTemplate' => $this->emailTemplate(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $newsletter = Newsletter::findOrFail($id);

        $newsletter->update($this->validated($request));

        return redirect('/admin/newsletters/' . $newsletter->newsletterID . '/edit')
            ->with('success', 'Newsletter saved.');
    }

    public function destroy($id)
    {
        Newsletter::findOrFail($id)->delete();

        return redirect('/admin/newsletters')->with('success', 'Newsletter deleted.');
    }

    /**
     * @return array{subject:string, body:string}
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'nullable|string',
        ]);

        return [
            'subject' => $data['subject'],
            'body' => $data['body'] ?? '',
        ];
    }

    /**
     * The Soccer Dads email wrapper rendered with placeholder tokens, so the
     * editor JS can drop in the live subject/body to preview and copy the exact
     * HTML recipients (Sendy) will see. Single source of truth: the email view.
     */
    private function emailTemplate(): string
    {
        return view('admin.newsletters.email', [
            'subject' => '__SUBJECT__',
            'body' => '__BODY__',
        ])->render();
    }
}
