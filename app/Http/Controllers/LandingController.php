<?php

namespace App\Http\Controllers;

use App\Models\MindOs\CertificationProgram;
use App\Models\MindOs\FoundationModule;
use App\Models\MindOs\MindBug;
use App\Models\MindOs\MindService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/** Public marketing pages: landing, sitemap.xml and robots.txt. */
class LandingController extends Controller
{
    public function index(): View
    {
        // The landing page is the most-hit URL — its catalogue is cached and
        // only the brand settings are read per request.
        $data = Cache::remember('mindos.landing', 600, fn () => [
            'bugs' => MindBug::orderBy('sort_order')->get(['id', 'code', 'name', 'tagline', 'description', 'color']),
            'services' => MindService::active()->get(['id', 'name', 'modality', 'stage', 'description', 'price', 'duration_minutes', 'is_group'])->groupBy('modality'),
            'programs' => CertificationProgram::orderBy('sort_order')->get(),
            'modules' => FoundationModule::published()->get(['category'])->countBy('category'),
        ]);

        return view('frontend.landing', $data);
    }

    public function sitemap(): Response
    {
        $xml = Cache::remember('mindos.sitemap', 3600, function () {
            $urls = [
                [url('/'), '1.0', 'weekly'],
                [route('register'), '0.8', 'monthly'],
                [route('login'), '0.5', 'monthly'],
            ];
            $now = now()->toAtomString();

            $body = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
                . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            foreach ($urls as [$loc, $priority, $freq]) {
                $body .= '  <url><loc>' . e($loc) . '</loc><lastmod>' . $now . '</lastmod>'
                    . '<changefreq>' . $freq . '</changefreq><priority>' . $priority . '</priority></url>' . "\n";
            }

            return $body . '</urlset>' . "\n";
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function robots(): Response
    {
        $body = implode("\n", [
            'User-agent: *',
            'Allow: /$',
            'Allow: /admin/login',
            'Allow: /admin/register',
            'Allow: /assets/',
            'Disallow: /admin/',
            'Disallow: /app/',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
            '',
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
