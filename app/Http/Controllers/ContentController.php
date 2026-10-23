<?php

namespace App\Http\Controllers;

class ContentController extends Controller
{
    public function index()
    {
        return view('content.index');
    }

    public function sitemap()
    {
        $static = [route('home'), route('web'), route('vps.index'), route('content.index'), route('termos.service'), route('privacidade'), route('reembolso'), route('vps.linux'), route('vps.windows'), route('help.index')];
        foreach (['revenda', 'dominios', 'infraestrutura', 'sobre', 'faq', 'contato', 'sla'] as $page) {
            $static[] = route('pages.show', $page);
        }
        foreach (array_keys(config('help.articles', [])) as $slug) {
            $static[] = route('help.show', $slug);
        }

        return response()->view('seo.sitemap', compact('static'))->header('Content-Type', 'application/xml');
    }
}
