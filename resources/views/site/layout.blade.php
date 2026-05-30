<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $pageTitle = $seoTitle ?? tkey('website.seo.default_title');
        $pageDescription = $seoDescription ?? tkey('website.seo.default_description');
        $openGraphTitle = $ogTitle ?? $pageTitle;
        $openGraphDescription = $ogDescription ?? $pageDescription;
        $openGraphUrl = $canonical ?? url()->current();
        $openGraphType = $ogType ?? 'website';
    @endphp
    <meta name="description" content="{{ $pageDescription }}">
    @if (($isIndexable ?? true) === false)
        <meta name="robots" content="noindex,nofollow">
    @endif
    @isset($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endisset
    <meta property="og:title" content="{{ $openGraphTitle }}">
    <meta property="og:description" content="{{ $openGraphDescription }}">
    <meta property="og:url" content="{{ $openGraphUrl }}">
    <meta property="og:type" content="{{ $openGraphType }}">
    @isset($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endisset
    <title>{{ $pageTitle }}</title>

    @vite(['resources/scss/site.scss', 'resources/js/site.js'])
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
                <a href="{{ route('website.contacts') }}">{{ tkey('website.nav.contacts') }}</a>
                <details class="nav-more">
                    <summary>{{ tkey('website.actions.show_more') }}</summary>
                    <div class="nav-more-panel">
                        <a href="{{ route('site.instructors') }}">{{ tkey('website.nav.instructors') }}</a>
                        <a href="{{ route('site.fleet') }}">{{ tkey('website.nav.fleet') }}</a>
                        <a href="{{ route('site.reviews') }}">{{ tkey('website.nav.reviews') }}</a>
                        <a href="{{ route('site.blog.index') }}">{{ tkey('website.nav.blog') }}</a>
                    </div>
                </details>
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
            <details class="mobile-menu">
                <summary aria-label="{{ tkey('website.navigation.main') }}">
                    <span class="menu-icon" aria-hidden="true"></span>
                    <span class="sr-only">{{ tkey('website.navigation.main') }}</span>
                </summary>
                <div class="mobile-menu-panel">
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
            </details>
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
