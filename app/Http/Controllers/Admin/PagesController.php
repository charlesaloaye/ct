<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ValidatesSettings;
use App\Http\Controllers\Controller;
use App\Support\MediaUrl;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Pages & SEO: the copy of every section (headings, leads, buttons) and each page's search title. */
class PagesController extends Controller
{
    use ValidatesSettings;

    public function edit(): Response
    {
        $s = SiteSettings::all();

        return Inertia::render('admin/Pages', [
            'about' => $s['about'],
            'sections' => $s['sections'],
            'seo' => $s['seo'],
            'shareImageUrl' => MediaUrl::for($s['seo']['shareImagePath']),
            'meta' => ['title' => 'Pages & SEO · Admin'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->saveGroups($request, ['about', 'sections', 'seo']);
        Inertia::flash('toast', 'Pages & SEO saved');

        return back();
    }
}
