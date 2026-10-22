<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Kenali dan dukung perawatan pohon di sekitarmu melalui Hijauku.">
    <title>Hijauku — Adopsi pohon, rawat pertumbuhannya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body id="beranda" class="min-h-screen bg-[#f7f8f2] text-[#183b2c] antialiased">
    <header class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-5 lg:px-10">
        <a class="flex shrink-0 items-center gap-3" href="#beranda" aria-label="Hijauku, beranda">
            <span class="flex size-11 items-center justify-center rounded-2xl bg-[#1f6b45] text-white">
                <svg aria-hidden="true" viewBox="0 0 24 24" class="size-6 fill-none stroke-current" stroke-width="1.8">
                    <path d="M19.5 4.5c-7.2 0-12 2.8-12 8.1 0 3.1 2.3 5 5.2 5 5.1 0 6.8-6.1 6.8-13.1Z" />
                    <path d="M4.5 20c2.7-4.3 6.1-7.4 10.5-10" />
                </svg>
            </span>
            <span>
                <span class="block text-lg font-semibold tracking-tight">hijauku</span>
                <span class="block text-xs text-[#597465]">adopsi pohon</span>
            </span>
        </a>

        <nav class="hidden items-center gap-8 text-sm font-medium text-[#486557] md:flex" aria-label="Navigasi utama">
            <a class="transition hover:text-[#1f6b45]" href="#beranda">Beranda</a>
            <a class="transition hover:text-[#1f6b45]" href="#langkah">Katalog Pohon</a>
            <a class="transition hover:text-[#1f6b45]" href="#kegiatan">Kegiatan</a>
        </nav>

        <a class="rounded-full border border-[#c9d7ca] bg-white px-5 py-2.5 text-sm font-semibold text-[#28573e] transition hover:bg-[#eff4ec]" href="{{ url('/app') }}">
            Masuk
        </a>
    </header>

    <main>
        <section class="mx-auto grid max-w-7xl items-center gap-12 px-6 pb-16 pt-10 lg:grid-cols-[1.05fr_0.95fr] lg:px-10 lg:pb-24 lg:pt-16">
            <div>
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#d7e1d6] bg-white/80 px-4 py-2 text-sm text-[#486557]">
                    <span class="size-2 rounded-full bg-[#77a77d]"></span>
                    Adopsi pohon tanpa pembayaran
                </p>
                <h1 class="max-w-2xl text-4xl font-semibold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                    Kenali dan Dukung Perawatan Pohon di Sekitarmu
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-[#597465]">
                    Ajukan adopsi pohon tanpa biaya dan ikuti perkembangannya melalui catatan perawatan yang dibagikan oleh tim penghijauan.
                </p>
                <div class="mt-8 flex flex-wrap items-center gap-4">
                    <a class="rounded-full bg-[#1f6b45] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-[#174f34]" href="#langkah">
                        Jelajahi Pohon
                    </a>
                    <a class="rounded-full border border-[#c9d7ca] bg-white px-6 py-3.5 text-sm font-semibold text-[#28573e] transition hover:bg-[#eff4ec]" href="#kegiatan">
                        Lihat Kegiatan
                    </a>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-xl" aria-hidden="true">
                <div class="absolute -left-5 top-10 size-24 rounded-full bg-[#dfe9d8] blur-2xl"></div>
                <div class="absolute -right-3 bottom-4 size-28 rounded-full bg-[#e6ebce] blur-2xl"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-[#dce4d8] bg-[#e8eee1] p-6 shadow-[0_28px_80px_-40px_rgba(24,59,44,0.35)] sm:p-9">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <p class="text-sm font-medium text-[#597465]">Hijauku</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">Tumbuh bersama, dirawat bersama</p>
                        </div>
                        <span class="rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-[#417450]">Pilih · Rawat · Pantau</span>
                    </div>
                    <div class="mt-7 flex h-64 items-end justify-center overflow-hidden rounded-[1.5rem] bg-gradient-to-b from-[#d8e7d2] to-[#b6d0ae]">
                        <svg viewBox="0 0 360 250" class="h-full w-full" focusable="false">
                            <path d="M0 210c58-20 111-14 163 0s111 17 197-7v47H0z" fill="#86ad82"/>
                            <path d="M169 215c4-44 7-72 3-108h25c-4 36-2 65 5 108z" fill="#785b3e"/>
                            <path d="M184 147c-24-24-40-43-57-70M187 130c27-24 42-43 60-73M183 107c-2-23-1-39 7-62" fill="none" stroke="#785b3e" stroke-linecap="round" stroke-width="12"/>
                            <circle cx="118" cy="68" r="34" fill="#4f8d5d"/>
                            <circle cx="158" cy="52" r="39" fill="#3c8051"/>
                            <circle cx="199" cy="44" r="43" fill="#2f7148"/>
                            <circle cx="239" cy="66" r="36" fill="#4b8957"/>
                            <circle cx="204" cy="87" r="36" fill="#5b9863"/>
                            <circle cx="157" cy="88" r="34" fill="#6aa26c"/>
                            <circle cx="79" cy="209" r="5" fill="#f3f5e8"/>
                            <circle cx="274" cy="197" r="4" fill="#f3f5e8"/>
                            <circle cx="292" cy="219" r="3" fill="#f3f5e8"/>
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <section id="langkah" class="scroll-mt-8 border-y border-[#e2e8df] bg-white/70">
            <div class="mx-auto max-w-7xl px-6 py-14 lg:px-10 lg:py-16">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold text-[#77a77d]">Langkah mudah</p>
                    <h2 class="mt-3 text-2xl font-semibold tracking-tight sm:text-3xl">Dukung pohon lewat perawatan yang berkelanjutan.</h2>
                </div>
                <div class="mt-9 grid gap-8 md:grid-cols-3">
                    <article>
                        <p class="text-sm font-semibold text-[#77a77d]">01</p>
                        <h3 class="mt-3 text-xl font-semibold">Pilih Pohon</h3>
                        <p class="mt-2 leading-7 text-[#597465]">Kenali pohon dan lokasi tanamnya dari katalog Hijauku.</p>
                    </article>
                    <article>
                        <p class="text-sm font-semibold text-[#77a77d]">02</p>
                        <h3 class="mt-3 text-xl font-semibold">Ajukan Adopsi</h3>
                        <p class="mt-2 leading-7 text-[#597465]">Sampaikan dukunganmu tanpa pembayaran dan tunggu kesepakatan dari pengelola.</p>
                    </article>
                    <article id="kegiatan" class="scroll-mt-8">
                        <p class="text-sm font-semibold text-[#77a77d]">03</p>
                        <h3 class="mt-3 text-xl font-semibold">Pantau Perawatan</h3>
                        <p class="mt-2 leading-7 text-[#597465]">Ikuti perkembangan dan kegiatan lapangan dari catatan yang telah ditinjau.</p>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-7 text-sm text-[#708579] sm:flex-row sm:items-center sm:justify-between lg:px-10">
        <span>Hijauku · Adopsi dan pemeliharaan pohon komunitas</span>
        <a class="font-medium text-[#486557] transition hover:text-[#1f6b45]" href="#beranda">Kembali ke atas</a>
    </footer>
</body>
</html>
