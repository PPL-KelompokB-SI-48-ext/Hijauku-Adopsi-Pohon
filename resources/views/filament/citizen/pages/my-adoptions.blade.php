<x-filament-panels::page>
    @php
        $citizenId = auth()->id();

        $requestCount = \App\Models\AdoptionRequest::query()
            ->where('requester_id', $citizenId)
            ->count();

        $agreementQuery = \App\Models\AdoptionAgreement::query()
            ->where('adopter_id', $citizenId);

        $agreementCount = (clone $agreementQuery)->count();

        $pendingAgreementCount = (clone $agreementQuery)
            ->where('status', 'awaiting_acceptance')
            ->count();

        $citizen = auth()->user();

        $accountLabel = filled($citizen?->email_verified_at)
            ? 'Akun Terverifikasi: '.$citizen->name
            : 'Akun: '.$citizen->name;
    @endphp

    <style>
        .m11-page {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .m11-summary {
            position: absolute;
            top: -57px;
            right: 170px;
            z-index: 5;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .m11-summary-card {
            display: flex;
            min-width: 126px;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            background: white;
            box-shadow: 0 1px 2px rgb(15 23 42 / 4%);
        }

        .m11-summary-card p {
            margin: 0;
            font-size: 10px;
            line-height: 1.3;
        }

        .m11-summary-card strong {
            display: block;
            color: #1f2937;
            font-size: 11px;
            font-weight: 600;
        }

        .m11-tabs {
            display: flex;
            min-height: 36px;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 4px 9px;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            background: white;
        }

        .m11-tab-list {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .m11-tab {
            display: inline-flex;
            min-height: 27px;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 6px;
            color: #64748b;
            font-size: 11px;
            font-weight: 500;
            white-space: nowrap;
            transition: background .15s ease;
        }

        .m11-tab:hover {
            background: #f1f5f9;
        }

        .m11-tab-active,
        .m11-tab-active:hover {
            background: #15803d;
            color: white;
        }

        .m11-tab-count {
            display: inline-flex;
            min-width: 15px;
            height: 15px;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            border-radius: 999px;
            background: #e2e8f0;
            color: #475569;
            font-size: 9px;
        }

        .m11-tab-active .m11-tab-count {
            background: rgb(255 255 255 / 22%);
            color: white;
        }

        .m11-verification {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #64748b;
            font-size: 10px;
            white-space: nowrap;
        }

        .m11-table-card {
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: white;
        }

        .m11-account {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 6px;
            padding: 9px 14px 0;
            color: #64748b;
            font-size: 10px;
        }

        .m11-table-content {
            padding: 3px 9px 8px;
        }

        .m11-table-content .fi-ta-header-toolbar {
            padding: 5px 7px !important;
            gap: 7px !important;
        }

        .m11-table-content .fi-ta-header-cell {
            padding: 8px 9px !important;
            font-size: 9px !important;
            letter-spacing: .035em;
        }

        .m11-table-content .fi-ta-cell {
            padding: 9px !important;
            font-size: 11px !important;
        }

        .m11-table-content .fi-input-wrp,
        .m11-table-content input,
        .m11-table-content select {
            min-height: 31px !important;
            font-size: 10px !important;
        }

        .m11-information {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .m11-information-card {
            display: flex;
            min-height: 72px;
            align-items: flex-start;
            gap: 9px;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            background: white;
        }

        .m11-information-icon {
            display: flex;
            width: 25px;
            height: 25px;
            flex: 0 0 25px;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
        }

        .m11-information-card h3 {
            margin: 1px 0 3px;
            color: #1f2937;
            font-size: 12px;
            font-weight: 600;
            line-height: 1.3;
        }

        .m11-information-card p {
            margin: 0;
            color: #64748b;
            font-size: 10px;
            line-height: 1.4;
        }

        .dark .m11-summary-card,
        .dark .m11-tabs,
        .dark .m11-table-card,
        .dark .m11-information-card {
            border-color: #374151;
            background: #111827;
        }

        .dark .m11-summary-card strong,
        .dark .m11-information-card h3 {
            color: #f9fafb;
        }

        .dark .m11-information-card p {
            color: #9ca3af;
        }

        @media (max-width: 1023px) {
            .m11-summary {
                position: static;
                justify-content: flex-end;
            }
        }

        @media (max-width: 767px) {
            .m11-information {
                grid-template-columns: 1fr;
            }

            .m11-tabs {
                align-items: flex-start;
                flex-direction: column;
            }

            .m11-tab-list {
                flex-wrap: wrap;
            }

            .m11-verification {
                white-space: normal;
            }

            .m11-summary {
                justify-content: flex-start;
                flex-wrap: wrap;
            }
        }
    </style>

    <div class="m11-page">

        {{-- Dua ringkasan kecil di kanan header --}}
        <div class="m11-summary">
            <div class="m11-summary-card">
                <span class="h-2 w-2 shrink-0 rounded-full bg-orange-600"></span>
                <div>
                    <p class="text-gray-500">Perlu Keputusan</p>
                    <strong>{{ $pendingAgreementCount }} Berkas Tertunda</strong>
                </div>
            </div>

            <div class="m11-summary-card">
                <span class="h-2 w-2 shrink-0 rounded-full bg-sky-600"></span>
                <div>
                    <p class="text-gray-500">Total Kesepakatan</p>
                    <strong>{{ $agreementCount }} Rekaman Sah</strong>
                </div>
            </div>
        </div>

        {{-- Tab horizontal --}}
        <div class="m11-tabs">
            <div class="m11-tab-list">
                <button
                    type="button"
                    wire:click="switchTab('requests')"
                    @class([
                        'm11-tab',
                        'm11-tab-active' => $this->activeTab === 'requests',
                    ])
                >
                    <x-heroicon-o-clipboard-document-check class="h-3.5 w-3.5" />
                    <span>Permohonan Adopsi</span>
                    <span class="m11-tab-count">{{ $requestCount }}</span>
                </button>

                <button
                    type="button"
                    wire:click="switchTab('agreements')"
                    @class([
                        'm11-tab',
                        'm11-tab-active' => $this->activeTab === 'agreements',
                    ])
                >
                    <x-heroicon-o-hand-raised class="h-3.5 w-3.5" />
                    <span>Kesepakatan Adopsi</span>
                    <span class="m11-tab-count">{{ $agreementCount }}</span>
                </button>
            </div>

            <div class="m11-verification">
                <x-heroicon-o-shield-check class="h-3.5 w-3.5 text-green-600" />
                <span>Verifikasi Otoritas Dinas Lingkungan & Pengelola Hijauku</span>
            </div>
        </div>

        {{-- Panel tabel --}}
        <div class="m11-table-card">
            <div class="m11-account">
                <span class="h-1.5 w-1.5 rounded-full {{ filled($citizen?->email_verified_at) ? 'bg-green-600' : 'bg-gray-400' }}"></span>
                <span>{{ $accountLabel }}</span>
            </div>

            <div class="m11-table-content">
                {{ $this->table }}
            </div>
        </div>

        {{-- Tiga kartu informasi, hanya satu kali --}}
        <div class="m11-information">

            <div class="m11-information-card">
                <span class="m11-information-icon bg-green-50 text-green-700 dark:bg-green-950 dark:text-green-400">
                    <x-heroicon-o-hand-raised class="h-4 w-4" />
                </span>
                <div>
                    <h3>Bukan Kepemilikan Fisik</h3>
                    <p>
                        Adopsi merupakan komitmen kepedulian lingkungan dan gotong royong warga, bukan kepemilikan aset tanah atau pohon.
                    </p>
                </div>
            </div>

            <div class="m11-information-card">
                <span class="m11-information-icon bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-400">
                    <x-heroicon-o-map-pin class="h-4 w-4" />
                </span>
                <div>
                    <h3>Pemantauan & Kunjungan</h3>
                    <p>
                        Warga berhak meninjau pohon adopsi di lokasi dan mencatat laporan kondisi melalui modul Pengamatan & Masalah.
                    </p>
                </div>
            </div>

            <div class="m11-information-card">
                <span class="m11-information-icon bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400">
                    <x-heroicon-o-clipboard-document-check class="h-4 w-4" />
                </span>
                <div>
                    <h3>Tanggung Jawab Bersama</h3>
                    <p>
                        Petugas lapangan Hijauku mendampingi jadwal pemupukan dan penyiraman berkala sepanjang masa kesepakatan.
                    </p>
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page>