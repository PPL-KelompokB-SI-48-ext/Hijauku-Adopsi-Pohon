<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk - Hijauku</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f7f3] text-slate-900 antialiased">
    <main class="mx-auto flex min-h-screen max-w-6xl items-center justify-center px-5 py-10 sm:px-8">
        <section class="grid w-full overflow-hidden rounded-3xl bg-white shadow-xl shadow-emerald-950/5 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="hidden min-h-[34rem] flex-col justify-between bg-[#14532d] p-10 text-white lg:flex xl:p-14">
                <a class="flex items-center gap-3" href="{{ route('home') }}">
                    <img class="h-11 w-11 rounded-full bg-white object-cover p-1" src="{{ asset('images/hijauku-brand-sheet.png') }}" alt="">
                    <span class="text-xl font-semibold tracking-tight">Hijauku</span>
                </a>

                <div class="max-w-md">
                    <p class="text-sm font-medium text-emerald-100">Kelola adopsi pohon bersama</p>
                    <h1 class="mt-4 text-4xl font-semibold leading-tight tracking-tight">Selamat datang kembali.</h1>
                    <p class="mt-4 leading-7 text-emerald-50/85">Masuk untuk melanjutkan pekerjaan sesuai peran Anda di Hijauku.</p>
                </div>

                <p class="text-sm text-emerald-100/80">Pilih, rawat, dan pantau pohon komunitas.</p>
            </div>

            <div class="flex items-center px-6 py-10 sm:px-10 lg:px-12">
                <div class="mx-auto w-full max-w-md">
                    <a class="mb-10 inline-flex items-center gap-3 lg:hidden" href="{{ route('home') }}">
                        <img class="h-10 w-10 rounded-full bg-emerald-50 object-cover p-1" src="{{ asset('images/hijauku-brand-sheet.png') }}" alt="">
                        <span class="text-lg font-semibold text-emerald-800">Hijauku</span>
                    </a>

                    <p class="text-sm font-medium text-emerald-700">Portal Hijauku</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight">Masuk ke akun</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan email dan kata sandi akun yang sudah terdaftar. Anda akan diarahkan ke panel sesuai peran akun.</p>

                    <form class="mt-8 space-y-5" method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-medium" for="email">Email</label>
                            <input
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"
                                id="email"
                                name="email"
                                type="email"
                                value="{{ old('email') }}"
                                autocomplete="username"
                                inputmode="email"
                                required
                                autofocus
                                aria-describedby="email-error"
                            >
                            @error('email')
                                <p class="mt-2 text-sm text-red-700" id="email-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium" for="password">Kata sandi</label>
                            <input
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/10"
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                            >
                            @error('password')
                                <p class="mt-2 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input class="rounded border-slate-300 text-emerald-700 focus:ring-emerald-700" type="checkbox" name="remember" value="1">
                            Ingat saya di perangkat ini
                        </label>

                        <button class="w-full rounded-xl bg-emerald-800 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-700/20" type="submit">
                            Masuk
                        </button>
                    </form>

                    <a class="mt-8 inline-flex text-sm font-medium text-emerald-800 hover:text-emerald-950" href="{{ route('home') }}">Kembali ke halaman utama</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
