<?php
// Kleeja Plugin
// pdf_viewer
// Version: 2.0
// Developer: Kleeja team

// Prevent illegal run
if (!defined('IN_PLUGINS_SYSTEM')) {
    exit();
}

// the plugin can be loaded twice in one request, so its constants are defined once
defined('PDF_VIEWER_VERSION') || define('PDF_VIEWER_VERSION', '2.0');

// PDF.js from jsDelivr: the library, its worker, the viewer components and their data files, all from this one version,
// the viewer components refuse to run with a library of another version. mozilla.github.io/pdf.js
defined('PDF_VIEWER_CDN') || define('PDF_VIEWER_CDN', 'https://cdn.jsdelivr.net/npm/pdfjs-dist@6.4.299/');

// Plugin Basic Information
$kleeja_plugin['pdf_viewer']['information'] = [
    // The casual name of this plugin, anything can a human being understands
    'plugin_title' => [
        'en' => 'PDF Viewer',
        'ar' => 'عارض ملفات PDF',
    ],
    // Who wrote this plugin?
    'plugin_developer' => 'Kleeja.net',
    // This plugin version
    'plugin_version' => PDF_VIEWER_VERSION,
    // Explain what is this plugin, why should I use it?
    'plugin_description' => [
        'en' =>
            'Let visitors read PDF files on their download page before they download them, with the PDF.js viewer by Mozilla.',
        'ar' => 'تتيح للزوار قراءة ملفات PDF في صفحة تحميلها قبل تنزيلها، بعارض PDF.js من Mozilla.',
    ],
    // Min version of Kleeja that's requiered to run this plugin
    'plugin_kleeja_version_min' => '4.0.0',
    // Max version of Kleeja that support this plugin, use 0 for unlimited
    'plugin_kleeja_version_max' => '4.9',
    // Should this plugin run before others?, 0 is normal, and higher number has high priority
    'plugin_priority' => 10,
];

//after installation message, you can remove it, it's not requiered
$kleeja_plugin['pdf_viewer']['first_run']['ar'] = "
تعرض هذه الإضافة ملفات PDF في صفحة تحميلها، تحت معلومات الملف، ولا تحتاج إلى أي إعداد.
ارفع ملف PDF وافتح صفحة تحميله لتراها.
<br>
<a href='./index.php?cp=s_help#help-pdf_viewer'>دليل الإضافة</a> -
<a href='https://github.com/kleeja/pdf_viewer/issues' target='_blank' rel='noopener'>الإبلاغ عن مشكلة</a>
";

$kleeja_plugin['pdf_viewer']['first_run']['en'] = "
This plugin shows PDF files on their download page, under the file details, and needs no setup.
Upload a PDF file and open its download page to see it.
<br>
<a href='./index.php?cp=s_help#help-pdf_viewer'>Plugin guide</a> -
<a href='https://github.com/kleeja/pdf_viewer/issues' target='_blank' rel='noopener'>Report a problem</a>
";

// Plugin Installation function
$kleeja_plugin['pdf_viewer']['install'] = function ($plg_id) {
    foreach (pdf_viewer_translations() as $lang_id => $words) {
        add_olang($words, $lang_id, $plg_id);
    }
};

// the words are written again, so a new version brings its own (1.x had none), with queries of its own:
// add_olang(), delete_olang() and delete_cache() run hooks, and the plugins are still loading.
// $kleeja_plugin isn't a global, Kleeja includes this file in a method, so the closure takes the information above with it
$kleeja_plugin['pdf_viewer']['update'] = function ($old_version, $new_version) use ($kleeja_plugin) {
    global $SQL, $dbprefix;

    $result = $SQL->build([
        'SELECT' => 'plg_id',
        'FROM' => "{$dbprefix}plugins",
        'WHERE' => 'plg_name = :name',
        'BIND' => ['name' => 'pdf_viewer'],
    ]);

    $row = $SQL->fetch_array($result);
    $plg_id = (int) ($row['plg_id'] ?? 0);
    $SQL->freeresult($result);

    //a plg_id of 0 would delete the words of the core and of every plugin
    if ($plg_id > 0) {
        $SQL->build([
            'DELETE' => "{$dbprefix}lang",
            'WHERE' => 'plg_id = :plg_id',
            'BIND' => ['plg_id' => $plg_id],
        ]);

        foreach (pdf_viewer_translations() as $lang_id => $words) {
            foreach ($words as $word => $trans) {
                $SQL->build([
                    'INSERT' => 'word, trans, lang_id, plg_id',
                    'INTO' => "{$dbprefix}lang",
                    'VALUES' => ':word, :trans, :lang_id, :plg_id',
                    'BIND' => ['word' => $word, 'trans' => $trans, 'lang_id' => $lang_id, 'plg_id' => $plg_id],
                ]);
            }
        }
    }

    // the developer and the description that the old versions were installed with,
    // the list of plugins shows them while the plugin is disabled
    if (version_compare($old_version, '2.0', '<')) {
        $SQL->build([
            'UPDATE' => "{$dbprefix}plugins",
            'SET' => 'plg_author = :author, plg_dsc = :description',
            'WHERE' => 'plg_name = :name',
            'BIND' => [
                'author' => 'Kleeja.net',
                'description' => kleeja_html_encode(
                    $kleeja_plugin['pdf_viewer']['information']['plugin_description']['en'],
                ),
                'name' => 'pdf_viewer',
            ],
        ]);
    }

    // the download template is compiled with the viewer in it, so the copies with the old viewer are deleted,
    // and the cached words. They are PHP files, and OPcache would keep running the old ones for a while,
    // or until PHP restarts when it doesn't check the files. Before a file is deleted,
    // OPcache finds a relative path only when the file exists
    $cached = array_merge(glob(PATH . 'cache/tpl_download*.php') ?: [], glob(PATH . 'cache/data_lang*.php') ?: []);

    foreach ($cached as $file) {
        if (file_exists($file)) {
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($file, true);
            }

            @unlink($file);
        }
    }
};

// Plugin Uninstallation, function to be called at unistalling
$kleeja_plugin['pdf_viewer']['uninstall'] = function ($plg_id) {
    //a plg_id of 0 would delete the words of the core and of every plugin
    if ((int) $plg_id > 0) {
        foreach (array_keys(pdf_viewer_translations()) as $lang_id) {
            delete_olang(null, $lang_id, (int) $plg_id);
        }
    }
};

// Plugin functions
$kleeja_plugin['pdf_viewer']['functions'] = [
    // the viewer in the download template, under the file, shown by b4_showsty_downlaod_id_filename.
    // assets/viewer.js fills it with PDF.js: the toolbar works the pages and the zoom,
    // and the container holds the pages that PDF.js draws, the box of the container needs a height of its own
    'style_parse_func' => function ($args) {
        if ($args['template_name'] !== 'download') {
            return;
        }

        $html =
            $args['html'] .
            '
<IF NAME="show_pdf_viewer_code">
<div class="kj-pdf row justify-content-center" data-kj-pdf data-src="{pdf_path}" data-cdn="{pdf_viewer_cdn}"
    data-error="{olang.PDF_VIEWER_ERROR|e}" data-error-password="{olang.PDF_VIEWER_ERROR_PASSWORD|e}">
    <div class="col-lg-10 col-xl-9">
        <section class="kj-pdf-box" aria-label="{olang.PDF_VIEWER_TITLE|e}" data-kj-pdf-box>
            <div class="kj-pdf-toolbar" role="toolbar" aria-label="{olang.PDF_VIEWER_TOOLBAR|e}">
                <div class="kj-pdf-group">
                    <button type="button" class="kj-pdf-btn" data-kj-pdf-action="previous" title="{olang.PDF_VIEWER_PREVIOUS|e}" aria-label="{olang.PDF_VIEWER_PREVIOUS|e}" disabled>
                        <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="kj-pdf-btn" data-kj-pdf-action="next" title="{olang.PDF_VIEWER_NEXT|e}" aria-label="{olang.PDF_VIEWER_NEXT|e}" disabled>
                        <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <span class="kj-pdf-pages">
                        <input type="number" class="kj-pdf-input" min="1" value="1" inputmode="numeric" title="{olang.PDF_VIEWER_PAGE|e}" aria-label="{olang.PDF_VIEWER_PAGE|e}" data-kj-pdf-page disabled>
                        <span>{olang.PDF_VIEWER_OF} <bdi data-kj-pdf-count>-</bdi></span>
                    </span>
                </div>
                <div class="kj-pdf-group">
                    <button type="button" class="kj-pdf-btn" data-kj-pdf-action="zoom-out" title="{olang.PDF_VIEWER_ZOOM_OUT|e}" aria-label="{olang.PDF_VIEWER_ZOOM_OUT|e}" disabled>
                        <i class="fa-solid fa-magnifying-glass-minus" aria-hidden="true"></i>
                    </button>
                    <select class="kj-pdf-select" title="{olang.PDF_VIEWER_ZOOM|e}" aria-label="{olang.PDF_VIEWER_ZOOM|e}" data-kj-pdf-scale disabled>
                        <option value="auto" selected>{olang.PDF_VIEWER_ZOOM_AUTO}</option>
                        <option value="page-actual">{olang.PDF_VIEWER_ZOOM_ACTUAL}</option>
                        <option value="page-fit">{olang.PDF_VIEWER_ZOOM_FIT}</option>
                        <option value="page-width">{olang.PDF_VIEWER_ZOOM_WIDTH}</option>
                        <option value="custom" hidden data-kj-pdf-custom></option>
                        <option value="0.5">50%</option>
                        <option value="0.75">75%</option>
                        <option value="1">100%</option>
                        <option value="1.25">125%</option>
                        <option value="1.5">150%</option>
                        <option value="2">200%</option>
                    </select>
                    <button type="button" class="kj-pdf-btn" data-kj-pdf-action="zoom-in" title="{olang.PDF_VIEWER_ZOOM_IN|e}" aria-label="{olang.PDF_VIEWER_ZOOM_IN|e}" disabled>
                        <i class="fa-solid fa-magnifying-glass-plus" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="kj-pdf-group" data-kj-pdf-screen hidden>
                    <button type="button" class="kj-pdf-btn" data-kj-pdf-action="presentation" title="{olang.PDF_VIEWER_PRESENTATION|e}" aria-label="{olang.PDF_VIEWER_PRESENTATION|e}" disabled>
                        <i class="fa-solid fa-person-chalkboard" aria-hidden="true"></i>
                    </button>
                    <button type="button" class="kj-pdf-btn" data-kj-pdf-action="fullscreen" title="{olang.PDF_VIEWER_FULLSCREEN|e}" aria-label="{olang.PDF_VIEWER_FULLSCREEN|e}" aria-pressed="false" disabled>
                        <i class="fa-solid fa-expand" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="kj-pdf-stage" data-kj-pdf-stage>
                <div class="kj-pdf-container" tabindex="0" aria-label="{olang.PDF_VIEWER_PAGES|e}" data-kj-pdf-container>
                    <div class="pdfViewer"></div>
                </div>
                <div class="kj-pdf-status" role="status" data-kj-pdf-status>
                    <span class="kj-pdf-spinner" aria-hidden="true"></span>
                    <i class="kj-pdf-icon fa-regular fa-file-pdf" aria-hidden="true"></i>
                    <span data-kj-pdf-message>{olang.PDF_VIEWER_LOADING}</span>
                </div>
            </div>
        </section>
    </div>
</div>
</IF>';

        return compact('html');
    },

    // decide if the file has the viewer, before the download page is shown
    'b4_showsty_downlaod_id_filename' => function ($args) {
        global $config;

        $show_pdf_viewer_code = false;
        $pdf_path = '';
        $file_info = $args['file_info'] ?? [];

        if (strtolower((string) ($file_info['type'] ?? '')) !== 'pdf') {
            return compact('show_pdf_viewer_code', 'pdf_path');
        }

        $show_pdf_viewer_code = true;

        // a full link, each part encoded, the page can be served at a pretty url
        $pdf_path =
            $config['siteurl'] .
            implode(
                '/',
                array_map('rawurlencode', explode('/', trim($file_info['folder'], '/') . '/' . $file_info['name'])),
            );

        // a plugin that keeps the files on another server (kj_ftp) gives their link here,
        // that server has to allow this site to read them (CORS)
        extract(runHook('plugin:pdf_viewer:do_display', get_defined_vars()));

        $pdf_path = htmlspecialchars($pdf_path, ENT_QUOTES);
        $pdf_viewer_cdn = htmlspecialchars(PDF_VIEWER_CDN, ENT_QUOTES);

        // the words of the viewer, in English for a language that has no translation of them
        $olang = (array) ($args['olang'] ?? []) + pdf_viewer_translations()['en'];

        return compact('show_pdf_viewer_code', 'pdf_path', 'pdf_viewer_cdn', 'olang');
    },

    // the files of the viewer, only in the page that shows it: the stylesheet of the PDF.js pages, the plugin's own,
    // and assets/viewer.js, a module, which runs after the page is parsed and loads PDF.js itself
    'Saaheader_links_func' => function ($args) {
        global $config;

        if (!defined('IN_DOWNLOAD') || empty($GLOBALS['show_pdf_viewer_code'])) {
            return;
        }

        $assets = $config['siteurl'] . KLEEJA_PLUGINS_FOLDER . '/pdf_viewer/assets/';

        $extra =
            $args['extra'] .
            '<link rel="stylesheet" href="' .
            PDF_VIEWER_CDN .
            'web/pdf_viewer.css">' .
            "\n" .
            '<link rel="stylesheet" href="' .
            $assets .
            'viewer.css?v=' .
            PDF_VIEWER_VERSION .
            '">' .
            "\n" .
            '<script type="module" src="' .
            $assets .
            'viewer.js?v=' .
            PDF_VIEWER_VERSION .
            '"></script>' .
            "\n";

        return compact('extra');
    },

    // the guide of the plugin on the help page of the control panel, its words are in language/help_{code}.php
    'admin_help_guides' => function ($args) {
        $help_guides = $args['help_guides'];
        $words = pdf_viewer_help_words();

        // the name of the plugin is the key, so Kleeja knows that the plugin has its guide, and shows its icon.
        // The plugin has no page in the control panel, so the guide has no 'page' and no 'link'
        $help_guides['pdf_viewer'] = [
            'group' => 'plugins',
            'title' => $words['PDF_VIEWER_HELP_TITLE'],
            'intro' => $words['PDF_VIEWER_HELP_INTRO'],
            // tips and warnings are shown beside the others on wide screens
            'sections' => [
                pdf_viewer_help_section($words, 'features', 'FEATURE'),
                pdf_viewer_help_section($words, 'steps', 'STEP'),
                pdf_viewer_help_section($words, 'faq', 'FAQ'),
                pdf_viewer_help_section($words, 'tips', 'TIP'),
                pdf_viewer_help_section($words, 'warnings', 'WARNING'),
            ],
        ];

        return compact('help_guides');
    },
];

/**
 * special functions
 */

if (!function_exists('pdf_viewer_translations')) {
    /**
     * the words of the viewer, they are added to the language table when the plugin is installed or updated
     * @return array
     */
    function pdf_viewer_translations()
    {
        return [
            'en' => [
                'PDF_VIEWER_TITLE' => 'PDF preview',
                'PDF_VIEWER_TOOLBAR' => 'Viewer controls',
                'PDF_VIEWER_PAGES' => 'Document pages',
                'PDF_VIEWER_PREVIOUS' => 'Previous page',
                'PDF_VIEWER_NEXT' => 'Next page',
                'PDF_VIEWER_PAGE' => 'Page',
                'PDF_VIEWER_OF' => 'of',
                'PDF_VIEWER_ZOOM' => 'Zoom',
                'PDF_VIEWER_ZOOM_IN' => 'Zoom in',
                'PDF_VIEWER_ZOOM_OUT' => 'Zoom out',
                'PDF_VIEWER_ZOOM_AUTO' => 'Automatic zoom',
                'PDF_VIEWER_ZOOM_ACTUAL' => 'Actual size',
                'PDF_VIEWER_ZOOM_FIT' => 'Fit page',
                'PDF_VIEWER_ZOOM_WIDTH' => 'Fit width',
                'PDF_VIEWER_PRESENTATION' => 'Presentation mode',
                'PDF_VIEWER_FULLSCREEN' => 'Full screen',
                'PDF_VIEWER_LOADING' => 'Loading the document…',
                'PDF_VIEWER_ERROR' => 'This file can\'t be previewed here. You can still download it.',
                'PDF_VIEWER_ERROR_PASSWORD' =>
                    'This file is protected with a password, so it can\'t be previewed here. Download it to open it.',
            ],
            'ar' => [
                'PDF_VIEWER_TITLE' => 'معاينة ملف PDF',
                'PDF_VIEWER_TOOLBAR' => 'أدوات العارض',
                'PDF_VIEWER_PAGES' => 'صفحات المستند',
                'PDF_VIEWER_PREVIOUS' => 'الصفحة السابقة',
                'PDF_VIEWER_NEXT' => 'الصفحة التالية',
                'PDF_VIEWER_PAGE' => 'الصفحة',
                'PDF_VIEWER_OF' => 'من',
                'PDF_VIEWER_ZOOM' => 'التكبير',
                'PDF_VIEWER_ZOOM_IN' => 'تكبير',
                'PDF_VIEWER_ZOOM_OUT' => 'تصغير',
                'PDF_VIEWER_ZOOM_AUTO' => 'تكبير تلقائي',
                'PDF_VIEWER_ZOOM_ACTUAL' => 'الحجم الفعلي',
                'PDF_VIEWER_ZOOM_FIT' => 'ملاءمة الصفحة',
                'PDF_VIEWER_ZOOM_WIDTH' => 'ملاءمة العرض',
                'PDF_VIEWER_PRESENTATION' => 'وضع العرض التقديمي',
                'PDF_VIEWER_FULLSCREEN' => 'ملء الشاشة',
                'PDF_VIEWER_LOADING' => 'جارٍ تحميل المستند…',
                'PDF_VIEWER_ERROR' => 'تتعذّر معاينة هذا الملف هنا، ولا يزال بإمكانك تنزيله.',
                'PDF_VIEWER_ERROR_PASSWORD' => 'هذا الملف محمي بكلمة مرور، لذا تتعذّر معاينته هنا. نزّله لتفتحه.',
            ],
        ];
    }

    /**
     * the words of the guide on the help page, in the language of the admin,
     * a word that the translation misses is shown in English
     * @return array
     */
    function pdf_viewer_help_words()
    {
        global $config;

        $words = (array) require __DIR__ . '/language/help_en.php';
        $language = preg_replace('/[^a-z0-9_-]/i', '', (string) ($config['language'] ?? ''));
        $translation = __DIR__ . "/language/help_{$language}.php";

        if ($language !== '' && $language !== 'en' && file_exists($translation)) {
            // in its own line, Prettier drops the brackets of (require $translation) + $words
            $translated = require $translation;
            $words = (array) $translated + $words;
        }

        return $words;
    }

    /**
     * a section of the guide from its numbered words, like PDF_VIEWER_HELP_TIP_1, .._TIP_2 ..
     * its title, when it has its own, is PDF_VIEWER_HELP_TIP_TITLE
     * @param  array  $words
     * @param  string $type  how Kleeja shows it: features, steps, tips, warnings or faq
     * @param  string $name  the name of its words, PDF_VIEWER_HELP_{name}_1
     * @return array
     */
    function pdf_viewer_help_section($words, $type, $name)
    {
        $prefix = 'PDF_VIEWER_HELP_' . $name;
        $section = ['type' => $type, 'title' => $words[$prefix . '_TITLE'] ?? '', 'items' => []];

        for ($n = 1; isset($words[$prefix . ($type === 'faq' ? '_Q_' : '_') . $n]); $n++) {
            $section['items'][] =
                $type === 'faq'
                    ? ['q' => $words[$prefix . '_Q_' . $n], 'a' => $words[$prefix . '_A_' . $n] ?? '']
                    : $words[$prefix . '_' . $n];
        }

        return $section;
    }
}
