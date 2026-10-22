<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Hijauku membantu komunitas merawat pohon setelah ditanam.">
    <title>Hijauku — Adopsi pohon, rawat pertumbuhannya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f6ef] text-[#183b2c] antialiased">
    <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
        <a class="flex items-center gap-3" href="{{ route('home') }}" aria-label="Hijauku, beranda">
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
        <nav class="hidden items-center gap-8 text-sm text-[#486557] md:flex" aria-label="Navigasi utama">
            <a class="transition hover:text-[#1f6b45]" href="#cara-kerja">Cara kerja</a>
            <a class="transition hover:text-[#1f6b45]" href="#tujuan">Tujuan</a>
        </nav>
        <a class="rounded-full bg-[#1f6b45] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#174f34]" href="{{ url('/app') }}">
            Masuk aplikasi
        </a>
    </header>

    <main>
        <section class="mx-auto grid max-w-7xl items-center gap-14 px-6 pb-20 pt-12 lg:grid-cols-[1.05fr_0.95fr] lg:px-10 lg:pb-28 lg:pt-16">
            <div>
                <p class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#d7e1d6] bg-white/70 px-4 py-2 text-sm text-[#486557]">
                    <span class="size-2 rounded-full bg-[#77a77d]"></span>
                    Merawat pohon bersama komunitas
                </p>
                <h1 class="max-w-2xl text-5xl font-semibold leading-[1.08] tracking-tight sm:text-6xl">
                    Pohon tumbuh baik saat dirawat bersama.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-[#597465]">
                    Hijauku membantu pengelola, petugas, dan warga mencatat adopsi, kegiatan lapangan, serta kondisi pohon dari waktu ke waktu.
                </p>
                <div class="mt-9 flex flex-wrap items-center gap-4">
                    <a class="rounded-full bg-[#1f6b45] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-[#174f34]" href="{{ url('/app') }}">
                        Buka ruang kerja
                    </a>
                    <a class="rounded-full border border-[#c9d7ca] bg-white/70 px-6 py-3.5 text-sm font-semibold text-[#28573e] transition hover:bg-white" href="#cara-kerja">
                        Lihat cara kerja
                    </a>
                </div>
                <p class="mt-8 text-sm text-[#708579]">Satu tempat untuk melihat rencana, pekerjaan, dan perkembangan pohon.</p>
            </div>

            <div class="relative mx-auto w-full max-w-xl">
                <div class="absolute -left-5 top-10 size-24 rounded-full bg-[#dfe9d8] blur-2xl"></div>
                <div class="absolute -right-3 bottom-4 size-28 rounded-full bg-[#e6ebce] blur-2xl"></div>
                <div class="relative overflow-hidden rounded-[2rem] border border-[#dce4d8] bg-[#e8eee1] p-7 shadow-[0_28px_80px_-40px_rgba(24,59,44,0.35)] sm:p-10">
                    <div class="flex items-start justify-between gap-5">
                        <div>
                            <p class="text-sm font-medium text-[#597465]">Perawatan yang tercatat</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight">Dari tanam hingga tumbuh</p>
                        </div>
                        <span class="rounded-full bg-white/80 px-3 py-1.5 text-xs font-medium text-[#417450]">Pantau bersama</span>
                    </div>
                    <div class="mt-8 flex h-64 items-end justify-center overflow-hidden rounded-[1.5rem] bg-gradient-to-b from-[#d8e7d2] to-[#b6d0ae]">
                        <svg role="img" aria-label="Ilustrasi pohon tumbuh" viewBox="0 0 360 250" class="h-full w-full">
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
                    <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-2xl bg-white/75 px-3 py-4">
                            <p class="text-xl font-semibold">01</p>
                            <p class="mt-1 text-xs text-[#597465]">Pohon</p>
                        </div>
                        <div class="rounded-2xl bg-white/75 px-3 py-4">
                            <p class="text-xl font-semibold">02</p>
                            <p class="mt-1 text-xs text-[#597465]">Rawat</p>
                        </div>
                        <div class="rounded-2xl bg-white/75 px-3 py-4">
                            <p class="text-xl font-semibold">03</p>
                            <p class="mt-1 text-xs text-[#597465]">Pantau</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="cara-kerja" class="border-y border-[#e2e8df] bg-white/65">
            <div class="mx-auto grid max-w-7xl gap-10 px-6 py-16 md:grid-cols-3 lg:px-10">
                <article>
                    <p class="text-sm font-semibold text-[#77a77d]">01 / Data dasar</p>
                    <h2 class="mt-3 text-xl font-semibold">Kenali pohon dan lokasinya</h2>
                    <p class="mt-3 leading-7 text-[#597465]">Pengelola mencatat program, lokasi, petak, jenis pohon, dan inventaris yang dirawat.</p>
                </article>
                <article>
                    <p class="text-sm font-semibold text-[#77a77d]">02 / Partisipasi</p>
                    <h2 class="mt-3 text-xl font-semibold">Hubungkan warga dengan perawatan</h2>
                    <p class="mt-3 leading-7 text-[#597465]">Warga mengajukan adopsi dan mengikuti kegiatan sesuai kesepakatan bersama.</p>
                </article>
                <article>
                    <p class="text-sm font-semibold text-[#77a77d]">03 / Perkembangan</p>
                    <h2 class="mt-3 text-xl font-semibold">Catat pekerjaan dan kondisi</h2>
                    <p class="mt-3 leading-7 text-[#597465]">Petugas mencatat perawatan dan pengamatan untuk ditinjau pengelola.</p>
                </article>
            </div>
        </section>

        <section id="tujuan" class="mx-auto flex max-w-7xl flex-col gap-8 px-6 py-16 md:flex-row md:items-center md:justify-between lg:px-10">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-[#77a77d]">Hijauku</p>
                <h2 class="mt-3 text-3xl font-semibold tracking-tight">Perawatan pohon tidak berhenti setelah penanaman.</h2>
                <p class="mt-4 leading-7 text-[#597465]">Ruang kerja ini disiapkan untuk mendukung pencatatan yang tertib dan mudah ditelusuri oleh tim penghijauan.</p>
            </div>
            <a class="inline-flex shrink-0 items-center justify-center rounded-full border border-[#c9d7ca] bg-white px-6 py-3.5 text-sm font-semibold text-[#28573e] transition hover:bg-[#eff4ec]" href="{{ url('/app') }}">
                Masuk ke Hijauku
            </a>
        </section>
    </main>

    <footer class="border-t border-[#e2e8df] px-6 py-6 text-center text-sm text-[#708579]">
        Hijauku · Adopsi dan pemeliharaan pohon komunitas
    </footer>
</body>
</html>
