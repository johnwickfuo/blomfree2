<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\Land;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        $staticPaths = ['/', '/about', '/contact', '/lands', '/kennel-farm', '/collections', '/gadgets', '/track'];
        foreach ($staticPaths as $path) {
            $urls[] = ['loc' => url($path), 'lastmod' => now()->toIso8601String(), 'changefreq' => 'weekly'];
        }

        Land::query()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Land $land) use (&$urls): void {
                $urls[] = [
                    'loc' => url('/lands/'.$land->slug),
                    'lastmod' => $land->updated_at?->toIso8601String(),
                    'changefreq' => 'weekly',
                ];
            });

        Animal::query()
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Animal $animal) use (&$urls): void {
                $urls[] = [
                    'loc' => url('/kennel-farm/'.$animal->slug),
                    'lastmod' => $animal->updated_at?->toIso8601String(),
                    'changefreq' => 'weekly',
                ];
            });

        Product::query()
            ->where('is_published', true)
            ->orderByDesc('updated_at')
            ->get(['slug', 'subsidiary', 'updated_at'])
            ->each(function (Product $product) use (&$urls): void {
                $urls[] = [
                    'loc' => url('/'.$product->subsidiary.'/'.$product->slug),
                    'lastmod' => $product->updated_at?->toIso8601String(),
                    'changefreq' => 'weekly',
                ];
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($u['loc'], ENT_XML1).'</loc>'."\n";
            if (! empty($u['lastmod'])) {
                $xml .= '    <lastmod>'.$u['lastmod'].'</lastmod>'."\n";
            }
            if (! empty($u['changefreq'])) {
                $xml .= '    <changefreq>'.$u['changefreq'].'</changefreq>'."\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
