<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($static as $url)<url><loc>{{ $url }}</loc></url>@endforeach
@foreach($contents as $content)<url><loc>{{ route('content.show', $content->slug) }}</loc><lastmod>{{ $content->updated_at->toAtomString() }}</lastmod></url>@endforeach
</urlset>
