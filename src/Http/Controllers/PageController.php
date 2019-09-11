<?php

namespace Dcat\Page\Http\Controllers;

use Dcat\Page\Documentation;
use Dcat\Page\Http\Assets;
use Illuminate\Routing\Controller;
use Dcat\Page\DcatPage;
use DcatPage as Fun;
use Symfony\Component\DomCrawler\Crawler;

class PageController extends Controller
{
    /**
     * 应用页面
     *
     * @param string $app
     * @param string $view
     * @return \Illuminate\View\View
     */
    public function page($app, $view = 'index')
    {
        DcatPage::init($app);

        return Fun\page($view);
    }

    /**
     * 应用文档页面
     *
     * @param $app
     * @param null $version
     * @param null $doc
     * @return mixed
     */
    public function doc($app, $version = null, $doc = null)
    {
        DcatPage::init($app);

        $page = $doc ?: Fun\config('doc.default', 'installation');

        if (!$version) {
            return redirect(Fun\url('docs/'.Fun\default_version().'/'.$page), 301);
        }

        $version = $version ?: Fun\default_version();

        $docs = Documentation::make();

        if (! $this->isVersion($docs, $version)) {
            return redirect(Fun\url('docs/'.Fun\default_version().'/'.$page), 301);
        }

        $sectionPage = $page ?: 'installation';
        $content = $docs->get($version, $sectionPage);

        if (is_null($content)) {
            return response()->view(Fun\view_name('pages.docs'), [
                'title' => 'Page not found',
                'index' => $docs->getIndex($version),
                'content' => Fun\view('partials.doc-missing'),
                'currentVersion' => $version,
                'versions' => $docs->getVersions(),
                'currentSection' => $page,
                'canonical' => null,
                'showSwitcher' => true,
            ], 404);
        }

        $title = (new Crawler($content))->filterXPath('//h1');

        $section = '';

        if ($docs->sectionExists($version, $page)) {
            $section .= '/'.$page;
        } elseif (! is_null($page)) {
            return redirect(Fun\url('/docs/'.$version));
        }

        $canonical = null;

        if ($docs->sectionExists(Fun\default_version(), $sectionPage)) {
            $canonical = 'docs/'.Fun\default_version().'/'.$sectionPage;
        }

        return Fun\page('docs')->with([
            'title' => count($title) ? $title->text() : null,
            'index' => $docs->getIndex($version),
            'content' => $content,
            'currentVersion' => $version,
            'versions' => $docs->getVersions(),
            'currentSection' => $section,
            'canonical' => $canonical,
            'showSwitcher' => true,
        ]);
    }

    /**
     * 应用静态资源加载
     *
     * @param $app
     * @param $path
     * @return \Illuminate\Http\Response
     */
    public function resource($app, $path)
    {
        DcatPage::init($app);

        if (strpos($path, '/') !== 0) {
            $path = 'public/'.$path;
        } else {
            $path = 'public'.$path;
        }

        $path = Fun\path($path);

        return Assets::send($path);
    }

    /**
     * 判断版本号
     *
     * @param Documentation $docs
     * @param $version
     * @return bool
     */
    protected function isVersion(Documentation $docs, $version)
    {
        return in_array($version, $docs->getVersions());
    }

}