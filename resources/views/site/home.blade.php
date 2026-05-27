<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $page->hero_summary }}">
    <title>{{ $page->title }}</title>

    @once
        <style>
            :root {
                color-scheme: light;
                --ink: #111827;
                --muted: #5b6472;
                --surface: #ffffff;
                --line: #d8dee8;
                --accent: #0f766e;
                --accent-strong: #115e59;
                --warm: #c2410c;
                --soft: #f6f7f9;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                color: var(--ink);
                background: var(--surface);
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                line-height: 1.5;
            }

            a {
                color: inherit;
            }

            .site-shell {
                min-height: 100vh;
                background: var(--surface);
            }

            .site-nav {
                position: absolute;
                z-index: 2;
                top: 0;
                left: 0;
                right: 0;
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: min(1120px, calc(100% - 32px));
                margin: 0 auto;
                padding: 22px 0;
                color: #ffffff;
            }

            .brand {
                font-weight: 800;
                letter-spacing: 0;
            }

            .nav-link {
                font-size: 0.92rem;
                font-weight: 700;
                text-decoration: none;
                border-bottom: 2px solid rgba(255, 255, 255, 0.65);
            }

            .hero {
                position: relative;
                display: grid;
                min-height: clamp(620px, 88vh, 760px);
                isolation: isolate;
                overflow: hidden;
                color: #ffffff;
                background-image:
                    linear-gradient(90deg, rgba(10, 15, 25, 0.88), rgba(10, 15, 25, 0.62) 42%, rgba(10, 15, 25, 0.08)),
                    url("{{ asset('images/driving-school-hero.png') }}");
                background-position: center;
                background-size: cover;
            }

            .hero-inner {
                width: min(1120px, calc(100% - 32px));
                margin: 0 auto;
                padding: 116px 0 128px;
                align-self: center;
            }

            .hero-copy {
                max-width: 650px;
            }

            .eyebrow {
                margin: 0 0 18px;
                color: #99f6e4;
                font-size: 0.82rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            h1,
            h2,
            h3,
            p {
                margin-top: 0;
            }

            h1 {
                max-width: 14ch;
                margin-bottom: 24px;
                font-size: clamp(2.75rem, 6.5vw, 5.4rem);
                line-height: 0.95;
                letter-spacing: 0;
            }

            .hero-summary {
                max-width: 58ch;
                margin-bottom: 34px;
                color: rgba(255, 255, 255, 0.86);
                font-size: clamp(1.05rem, 2vw, 1.24rem);
            }

            .hero-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;
            }

            .site-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 46px;
                padding: 0 18px;
                border: 1px solid var(--accent);
                border-radius: 8px;
                background: var(--accent);
                color: #ffffff;
                font-weight: 800;
                text-decoration: none;
            }

            .site-button-secondary {
                border-color: rgba(255, 255, 255, 0.55);
                background: rgba(255, 255, 255, 0.12);
                backdrop-filter: blur(10px);
            }

            .content-band {
                margin-top: -72px;
                padding: 0 0 80px;
            }

            .content-inner {
                width: min(1120px, calc(100% - 32px));
                margin: 0 auto;
            }

            .intro-panel {
                display: grid;
                grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
                gap: 36px;
                padding: 34px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--surface);
                box-shadow: 0 24px 70px rgba(15, 23, 42, 0.14);
            }

            .section-heading p {
                margin-bottom: 10px;
                color: var(--warm);
                font-size: 0.78rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .section-heading h2 {
                margin-bottom: 0;
                font-size: clamp(1.9rem, 4vw, 3rem);
                line-height: 1.04;
                letter-spacing: 0;
            }

            .intro-panel > p {
                margin: 0;
                color: var(--muted);
                font-size: 1.04rem;
            }

            .offer-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                margin-top: 26px;
            }

            .offer-card {
                min-height: 210px;
                padding: 24px;
                border: 1px solid var(--line);
                border-radius: 8px;
                background: var(--soft);
            }

            .offer-card span {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                margin-bottom: 22px;
                border-radius: 999px;
                background: var(--accent);
                color: #ffffff;
                font-size: 0.88rem;
                font-weight: 800;
            }

            .offer-card h3 {
                margin-bottom: 10px;
                font-size: 1.18rem;
                letter-spacing: 0;
            }

            .offer-card p {
                margin-bottom: 0;
                color: var(--muted);
            }

            @media (max-width: 760px) {
                .site-nav {
                    width: min(100% - 24px, 1120px);
                    padding-top: 18px;
                }

                .hero {
                    min-height: 700px;
                    background-position: 58% center;
                }

                .hero-inner {
                    width: min(100% - 24px, 1120px);
                    padding-top: 104px;
                }

                h1 {
                    max-width: 11ch;
                }

                .content-band {
                    margin-top: -54px;
                }

                .content-inner {
                    width: min(100% - 24px, 1120px);
                }

                .intro-panel {
                    grid-template-columns: 1fr;
                    gap: 18px;
                    padding: 22px;
                }

                .offer-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    @endonce
</head>
<body>
    <div class="site-shell">
        <nav class="site-nav" aria-label="Main navigation">
            <a class="brand" href="{{ route('site.home') }}">{{ $page->title }}</a>
            <a class="nav-link" href="{{ url('/admin') }}">Admin</a>
        </nav>

        <header class="hero">
            <div class="hero-inner">
                <div class="hero-copy">
                    @if ($page->eyebrow)
                        <p class="eyebrow">{{ $page->eyebrow }}</p>
                    @endif

                    <h1>{{ $page->hero_title }}</h1>
                    <p class="hero-summary">{{ $page->hero_summary }}</p>

                    <div class="hero-actions">
                        @if ($page->primary_button_label && $page->primary_button_url)
                            <x-site.button :href="$page->primary_button_url">
                                {{ $page->primary_button_label }}
                            </x-site.button>
                        @endif

                        @if ($page->secondary_button_label && $page->secondary_button_url)
                            <x-site.button :href="$page->secondary_button_url" variant="secondary">
                                {{ $page->secondary_button_label }}
                            </x-site.button>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <main id="content" class="content-band">
            <div class="content-inner">
                <section class="intro-panel" aria-labelledby="about-heading">
                    <x-site.section-heading
                        eyebrow="Content"
                        :title="$page->about_heading"
                    />
                    <p id="about-heading">{{ $page->about_body }}</p>
                </section>

                <section class="offer-grid" aria-label="Prepared sections">
                    @forelse ($offers as $offer)
                        <article class="offer-card">
                            <span>{{ $loop->iteration }}</span>
                            <h3>{{ $offer['title'] }}</h3>
                            <p>{{ $offer['body'] }}</p>
                        </article>
                    @empty
                        <article class="offer-card">
                            <span>0</span>
                            <h3>No sections published</h3>
                            <p>Add homepage sections from the Orchid dashboard.</p>
                        </article>
                    @endforelse
                </section>
            </div>
        </main>
    </div>
</body>
</html>
