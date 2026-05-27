<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $seoDescription ?? tkey('website.seo.default_description') }}">
    @if (($isIndexable ?? true) === false)
        <meta name="robots" content="noindex,nofollow">
    @endif
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <meta property="og:title" content="{{ $seoTitle ?? tkey('website.seo.default_title') }}">
    <meta property="og:description" content="{{ $seoDescription ?? tkey('website.seo.default_description') }}">
    @isset($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endisset
    <title>{{ $seoTitle ?? tkey('website.seo.default_title') }}</title>

    @once
        <style>
            :root {
                color-scheme: light;
                --ink: #131820;
                --muted: #5d6674;
                --surface: #ffffff;
                --soft: #f5f7fa;
                --line: #d9e0ea;
                --accent: #0f766e;
                --accent-strong: #115e59;
                --warm: #b45309;
                --danger: #b91c1c;
                --dark: #1f2937;
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                color: var(--ink);
                background: var(--surface);
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            a { color: inherit; }

            img { max-width: 100%; display: block; }

            h1, h2, h3, p { margin-top: 0; }

            .site-nav {
                position: sticky;
                z-index: 10;
                top: 0;
                border-bottom: 1px solid rgba(217, 224, 234, 0.84);
                background: rgba(255, 255, 255, 0.94);
                backdrop-filter: blur(14px);
            }

            .site-nav-inner {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                width: min(1180px, calc(100% - 32px));
                min-height: 72px;
                margin: 0 auto;
            }

            .brand {
                font-weight: 900;
                text-decoration: none;
                letter-spacing: 0;
                white-space: nowrap;
            }

            .nav-links {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 14px;
                color: var(--muted);
                font-size: 0.92rem;
                font-weight: 750;
            }

            .nav-links a {
                text-decoration: none;
            }

            .language-switcher {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin: 0;
            }

            .language-switcher label {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                color: var(--muted);
                font-size: 0.88rem;
                font-weight: 750;
            }

            .language-switcher select {
                width: auto;
                min-width: 96px;
                min-height: 36px;
                padding: 6px 10px;
                font-size: 0.88rem;
            }

            .language-switcher button {
                min-height: 36px;
                padding: 0 10px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #ffffff;
                color: var(--ink);
                font: inherit;
                font-size: 0.86rem;
                font-weight: 850;
                cursor: pointer;
            }

            .nav-cta,
            .button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                padding: 0 16px;
                border: 1px solid var(--accent);
                border-radius: 8px;
                background: var(--accent);
                color: #ffffff;
                font-weight: 850;
                text-decoration: none;
                cursor: pointer;
            }

            .button.secondary {
                border-color: var(--line);
                background: #ffffff;
                color: var(--ink);
            }

            .hero {
                position: relative;
                display: grid;
                min-height: clamp(620px, 84vh, 780px);
                overflow: hidden;
                color: #ffffff;
                background-image:
                    linear-gradient(90deg, rgba(11, 18, 32, 0.88), rgba(11, 18, 32, 0.60) 46%, rgba(11, 18, 32, 0.10)),
                    url("{{ asset('images/driving-school-hero.png') }}");
                background-position: center;
                background-size: cover;
            }

            .hero-inner,
            .section-inner {
                width: min(1180px, calc(100% - 32px));
                margin: 0 auto;
            }

            .hero-inner {
                display: grid;
                align-items: center;
                padding: 76px 0 118px;
            }

            .hero-copy { max-width: 680px; }

            .eyebrow {
                margin-bottom: 16px;
                color: #99f6e4;
                font-size: 0.8rem;
                font-weight: 900;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            h1 {
                max-width: 14ch;
                margin-bottom: 24px;
                font-size: clamp(2.6rem, 6.8vw, 5.5rem);
                line-height: 0.96;
                letter-spacing: 0;
            }

            .lead {
                max-width: 68ch;
                color: var(--muted);
                font-size: 1.06rem;
            }

            .hero .lead {
                color: rgba(255, 255, 255, 0.88);
                font-size: clamp(1.05rem, 2vw, 1.25rem);
            }

            .hero-actions,
            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
                margin-top: 28px;
            }

            .section {
                padding: 76px 0;
            }

            .section.soft {
                background: var(--soft);
            }

            .section.dark {
                background: var(--dark);
                color: #ffffff;
            }

            .section-head {
                display: grid;
                grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
                gap: 32px;
                align-items: end;
                margin-bottom: 30px;
            }

            .section-head h2 {
                margin-bottom: 0;
                font-size: clamp(1.9rem, 3.6vw, 3.1rem);
                line-height: 1.04;
                letter-spacing: 0;
            }

            .section-head p {
                margin-bottom: 0;
            }

            .grid {
                display: grid;
                gap: 18px;
            }

            .grid.two { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .grid.four { grid-template-columns: repeat(4, minmax(0, 1fr)); }

            .card {
                min-width: 0;
                padding: 24px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #ffffff;
            }

            .article-body {
                display: grid;
                gap: 16px;
            }

            .article-body h2,
            .article-body h3,
            .article-body h4 {
                margin: 16px 0 0;
                line-height: 1.15;
                letter-spacing: 0;
            }

            .article-body p,
            .article-body ul,
            .article-body ol,
            .article-body pre,
            .article-body table {
                margin: 0;
            }

            .article-body ul,
            .article-body ol {
                padding-left: 22px;
            }

            .article-body pre {
                overflow-x: auto;
                padding: 14px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #f8fafc;
            }

            .article-body code {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
                font-size: 0.9em;
            }

            .dark .card {
                border-color: rgba(255, 255, 255, 0.14);
                background: rgba(255, 255, 255, 0.08);
                color: #ffffff;
            }

            .kicker {
                color: var(--warm);
                font-size: 0.78rem;
                font-weight: 900;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .meta {
                color: var(--muted);
                font-size: 0.92rem;
            }

            .dark .meta,
            .dark .lead {
                color: rgba(255, 255, 255, 0.76);
            }

            .price {
                font-size: 1.55rem;
                font-weight: 900;
            }

            .stat {
                display: grid;
                gap: 4px;
            }

            .stat strong {
                font-size: clamp(2rem, 4vw, 3.4rem);
                line-height: 1;
            }

            .badge-list,
            .facts {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 14px;
            }

            .badge,
            .fact {
                display: inline-flex;
                align-items: center;
                min-height: 30px;
                padding: 4px 10px;
                border: 1px solid var(--line);
                border-radius: 999px;
                background: #ffffff;
                color: var(--muted);
                font-size: 0.84rem;
                font-weight: 750;
            }

            .dark .badge,
            .dark .fact {
                border-color: rgba(255, 255, 255, 0.18);
                background: rgba(255, 255, 255, 0.08);
                color: rgba(255, 255, 255, 0.82);
            }

            .table-wrap {
                overflow-x: auto;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #ffffff;
            }

            table {
                width: 100%;
                min-width: 720px;
                border-collapse: collapse;
            }

            th,
            td {
                padding: 14px 16px;
                border-bottom: 1px solid var(--line);
                text-align: left;
                vertical-align: top;
            }

            th {
                color: var(--muted);
                font-size: 0.78rem;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }

            tr:last-child td {
                border-bottom: 0;
            }

            .form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            label {
                display: grid;
                gap: 7px;
                color: var(--muted);
                font-size: 0.9rem;
                font-weight: 750;
            }

            input,
            select,
            textarea {
                width: 100%;
                min-height: 44px;
                padding: 10px 12px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: #ffffff;
                color: var(--ink);
                font: inherit;
            }

            textarea { min-height: 120px; resize: vertical; }

            .full { grid-column: 1 / -1; }

            .mt-18 { margin-top: 18px; }

            .error {
                color: var(--danger);
                font-size: 0.84rem;
            }

            .notice {
                padding: 14px 16px;
                border: 1px solid #99f6e4;
                border-radius: 8px;
                background: #ecfdf5;
                color: #065f46;
                font-weight: 750;
            }

            .inline-form {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 12px;
                align-items: end;
            }

            .inline-form .full {
                grid-column: 1 / -1;
            }

            .check-row {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .check-row input {
                width: auto;
                min-height: auto;
            }

            .map {
                display: grid;
                min-height: 320px;
                align-items: center;
                justify-items: center;
                border: 1px solid var(--line);
                border-radius: 8px;
                background:
                    linear-gradient(90deg, rgba(15, 118, 110, 0.09) 1px, transparent 1px),
                    linear-gradient(rgba(180, 83, 9, 0.10) 1px, transparent 1px),
                    #ffffff;
                background-size: 44px 44px;
            }

            .footer {
                padding: 36px 0;
                border-top: 1px solid var(--line);
                color: var(--muted);
            }

            .float-actions {
                position: fixed;
                right: 16px;
                bottom: 16px;
                z-index: 20;
                display: grid;
                gap: 8px;
            }

            .float-actions a {
                min-width: 128px;
                min-height: 40px;
                padding: 8px 12px;
                border-radius: 8px;
                background: var(--dark);
                color: #ffffff;
                font-size: 0.86rem;
                font-weight: 850;
                text-align: center;
                text-decoration: none;
            }

            @media (max-width: 880px) {
                .site-nav-inner {
                    align-items: flex-start;
                    flex-direction: column;
                    padding: 14px 0;
                }

                .nav-links {
                    justify-content: flex-start;
                }

                .section-head,
                .grid.two,
                .grid.three,
                .grid.four,
                .form-grid,
                .inline-form {
                    grid-template-columns: 1fr;
                }

                .hero {
                    min-height: 680px;
                    background-position: 60% center;
                }

                .hero-inner,
                .section-inner {
                    width: min(100% - 24px, 1180px);
                }

                .section {
                    padding: 54px 0;
                }

                .float-actions {
                    left: 12px;
                    right: 12px;
                    grid-template-columns: 1fr 1fr;
                }
            }
        </style>
    @endonce
</head>
<body>
    @php($locales = $availableLocales ?? collect())

    <nav class="site-nav" aria-label="{{ tkey('website.navigation.main') }}">
        <div class="site-nav-inner">
            <a class="brand" href="{{ route('website.home') }}">{{ tkey('website.brand.name') }}</a>
            <div class="nav-links">
                <a href="{{ route('website.home') }}">{{ tkey('website.nav.home') }}</a>
                <a href="{{ route('website.courses.index') }}">{{ tkey('website.nav.courses') }}</a>
                <a href="{{ route('website.pricing') }}">{{ tkey('website.nav.pricing') }}</a>
                <a href="{{ route('website.branches.index') }}">{{ tkey('website.nav.branches') }}</a>
                <a href="{{ route('website.home') }}#application-form">{{ tkey('website.nav.apply') }}</a>
                <a href="{{ route('site.instructors') }}">{{ tkey('website.nav.instructors') }}</a>
                <a href="{{ route('site.fleet') }}">{{ tkey('website.nav.fleet') }}</a>
                <a href="{{ route('site.reviews') }}">{{ tkey('website.nav.reviews') }}</a>
                <a href="{{ route('site.blog.index') }}">{{ tkey('website.nav.blog') }}</a>
                <a href="{{ route('website.contacts') }}">{{ tkey('website.nav.contacts') }}</a>
                @if($locales->isNotEmpty())
                    <form class="language-switcher" method="POST" action="{{ route('website.language.switch') }}">
                        @csrf
                        <label>
                            <span>{{ tkey('locale.switcher.label') }}</span>
                            <select name="locale" aria-label="{{ tkey('locale.switcher.label') }}" onchange="this.form.submit()">
                                @foreach($locales as $language)
                                    <option value="{{ $language->code }}" @selected(($currentLocale ?? app()->getLocale()) === $language->code)>
                                        {{ $language->native_name ?: $language->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit">{{ tkey('locale.switcher.submit') }}</button>
                    </form>
                @endif
                <a class="nav-cta" href="{{ url('/admin') }}">{{ tkey('website.nav.admin') }}</a>
            </div>
        </div>
    </nav>

    @yield('content')

    <footer class="footer">
        <div class="section-inner">
            <strong>{{ tkey('website.brand.name') }}</strong>
            <p class="meta">{{ tkey('website.footer.description') }}</p>
        </div>
    </footer>

    <div class="float-actions" aria-label="{{ tkey('website.actions.fast_contact') }}">
        <a href="{{ route('website.home') }}#application-form">{{ tkey('website.actions.online_chat') }}</a>
        <a href="{{ route('website.contacts') }}#callback">{{ tkey('website.actions.callback') }}</a>
    </div>
</body>
</html>
