<?php

namespace App\Http\Controllers;

class HelpCenterController extends Controller
{
    public function index()
    {
        return view('help.index', ['categories' => config('help.categories'), 'articles' => collect(config('help.articles'))]);
    }

    public function show(string $slug)
    {
        $article = config("help.articles.{$slug}");
        abort_unless($article, 404);

        $related = collect(config('help.articles'))
            ->filter(fn ($item, $key) => $item['category'] === $article['category'] && $key !== $slug)
            ->take(3);

        return view('help.show', compact('slug', 'article', 'related'));
    }
}
