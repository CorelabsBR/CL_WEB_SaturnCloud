<?php

namespace App\Http\Controllers;

use App\Models\MarketingContent;

class MarketingPublicController extends Controller
{
    public function index()
    {
        return view('content.index', ['contents' => MarketingContent::where('status', 'published')->whereNotNull('published_at')->latest('published_at')->paginate(12)]);
    }

    public function show(MarketingContent $content)
    {
        abort_unless($content->status === 'published' && $content->published_at?->isPast(), 404);
        return view('content.show', compact('content'));
    }

    public function sitemap()
    {
        $static = [route('home'), route('web'), route('vps.index'), route('content.index'), route('termos.service'), route('privacidade'), route('reembolso')];
        foreach (['revenda', 'dominios', 'infraestrutura', 'sobre', 'faq', 'contato', 'sla'] as $page) {
            $static[] = route('pages.show', $page);
        }
        $static[] = route('vps.linux');
        $static[] = route('vps.windows');
        $static[] = route('help.index');
        foreach (array_keys(config('help.articles', [])) as $slug) {
            $static[] = route('help.show', $slug);
        }
        $contents = MarketingContent::where('status', 'published')->whereNotNull('published_at')->get(['slug', 'updated_at']);
        return response()->view('seo.sitemap', compact('static', 'contents'))->header('Content-Type', 'application/xml');
    }
}
