<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @php
     $p = 0;
    @endphp
    @foreach ($data as $pages)
    @php
     $p += 0.1;
    @endphp
        <url>
            <loc>{{ 'https://www.timeupindia.com/'.$pages['url'] }}</loc>
            <lastmod>{{ $pages['created_at'] }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>{{$p}}</priority>
        </url>
    @endforeach
</urlset>