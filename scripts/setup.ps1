[CmdletBinding()]
param(
    [switch] $SkipDatabase,
    [switch] $AllowRemoteDatabase
)

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot

function Invoke-CheckedCommand {
    param(
        [Parameter(Mandatory)] [string] $Command,
        [Parameter(Mandatory)] [string[]] $Arguments
    )

    & $Command @Arguments

    if ($LASTEXITCODE -ne 0) {
        throw "Perintah gagal (exit code $LASTEXITCODE): $Command $($Arguments -join ' ')"
    }
}

function Get-DotEnvValue {
    param([Parameter(Mandatory)] [string] $Name)

    $line = Get-Content -LiteralPath '.env' | Where-Object {
        $_ -match "^\s*$([regex]::Escape($Name))\s*="
    } | Select-Object -Last 1

    if (-not $line) {
        return ''
    }

    $value = ($line -split '=', 2)[1].Trim()

    if ($value.Length -ge 2 -and $value[0] -eq $value[-1] -and $value[0] -in @('"', "'")) {
        $value = $value.Substring(1, $value.Length - 2)
    } else {
        $value = ($value -replace '\s+#.*$', '').Trim()
    }

    return $value
}

Push-Location $repoRoot

try {
    foreach ($tool in @('php', 'composer', 'node', 'npm')) {
        if (-not (Get-Command $tool -ErrorAction SilentlyContinue)) {
            throw "Perintah '$tool' belum ditemukan. Pasang prasyarat dari docs/local-setup.md, lalu buka terminal baru."
        }
    }

    $phpVersionText = (& php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;').Trim()
    if ($LASTEXITCODE -ne 0 -or [version] $phpVersionText -lt [version] '8.3') {
        throw "Hijauku membutuhkan PHP 8.3 atau lebih baru. Versi yang terdeteksi: $phpVersionText"
    }

    $phpModules = & php -m
    if ($LASTEXITCODE -ne 0) {
        throw 'Tidak dapat membaca daftar ekstensi PHP.'
    }

    $requiredModules = @('pdo_mysql', 'pdo_sqlite', 'sqlite3', 'mbstring', 'openssl', 'fileinfo', 'zip', 'xml', 'ctype', 'curl', 'tokenizer')
    $missingModules = @($requiredModules | Where-Object { $_ -notin $phpModules })
    if ($missingModules.Count -gt 0) {
        throw "Ekstensi PHP belum tersedia: $($missingModules -join ', '). Aktifkan lewat konfigurasi PHP yang digunakan terminal."
    }

    $composerVersionOutput = (& composer --version | Out-String).Trim()
    if ($LASTEXITCODE -ne 0 -or $composerVersionOutput -notmatch 'Composer version (?<major>\d+)') {
        throw "Tidak dapat membaca versi Composer: $composerVersionOutput"
    }

    if ([int] $Matches.major -lt 2) {
        throw "Hijauku membutuhkan Composer 2 atau lebih baru. Versi yang terdeteksi: $composerVersionOutput"
    }

    $nodeVersionText = (& node --version).Trim().TrimStart('v')
    if ($LASTEXITCODE -ne 0 -or [version] $nodeVersionText -lt [version] '22.0') {
        throw "Hijauku membutuhkan Node.js 22 atau lebih baru. Versi yang terdeteksi: $nodeVersionText"
    }

    if (-not (Test-Path -LiteralPath '.env')) {
        Copy-Item -LiteralPath '.env.example' -Destination '.env'
        Write-Host 'Dibuat .env dari .env.example. Sesuaikan kredensial database sebelum migrasi.'
    } else {
        Write-Host '.env yang sudah ada dipertahankan.'
    }

    $appEnvironment = Get-DotEnvValue -Name 'APP_ENV'
    if ($appEnvironment.Contains('$')) {
        throw 'APP_ENV harus berupa nilai literal di .env agar mode production dapat diperiksa dengan aman.'
    }

    if ($appEnvironment -eq 'production') {
        throw 'Setup lokal dihentikan karena APP_ENV bernilai production.'
    }

    Write-Host 'Memasang dependency PHP...'
    Invoke-CheckedCommand -Command 'composer' -Arguments @('install', '--prefer-dist', '--no-interaction')

    Write-Host 'Memasang dependency frontend...'
    Invoke-CheckedCommand -Command 'npm' -Arguments @('ci')

    if ([string]::IsNullOrWhiteSpace((Get-DotEnvValue -Name 'APP_KEY'))) {
        Write-Host 'APP_KEY kosong, membuat key lokal baru...'
        Invoke-CheckedCommand -Command 'php' -Arguments @('artisan', 'key:generate', '--force')
    }

    Write-Host 'Membangun aset frontend...'
    Invoke-CheckedCommand -Command 'npm' -Arguments @('run', 'build')

    if ($SkipDatabase) {
        Write-Host 'Setup dependency selesai. Database dilewati sesuai opsi -SkipDatabase.'
        return
    }

    $connection = Get-DotEnvValue -Name 'DB_CONNECTION'
    $hostName = Get-DotEnvValue -Name 'DB_HOST'
    $port = Get-DotEnvValue -Name 'DB_PORT'
    $databaseName = Get-DotEnvValue -Name 'DB_DATABASE'
    $databasePassword = Get-DotEnvValue -Name 'DB_PASSWORD'

    if ($connection -ne 'mysql') {
        throw "Setup migrasi tim memakai MySQL. DB_CONNECTION saat ini '$connection'. Periksa .env dan docs/local-setup.md."
    }

    if ([string]::IsNullOrWhiteSpace($databaseName)) {
        throw 'DB_DATABASE belum diisi pada .env.'
    }

    if ($databaseName.Contains('$')) {
        throw 'DB_DATABASE harus berupa nama database literal agar target migrasi yang ditampilkan sama dengan konfigurasi Laravel.'
    }

    if ($databasePassword -eq 'ganti-password-lokal-ini') {
        throw 'DB_PASSWORD masih berupa nilai contoh. Isi kredensial lokal di .env, lalu jalankan script lagi.'
    }

    $localHosts = @('127.0.0.1', 'localhost', '::1')
    if ($hostName -notin $localHosts -and -not $AllowRemoteDatabase) {
        throw "Host database '$hostName' bukan host lokal. Cek .env; untuk DB jarak jauh gunakan -AllowRemoteDatabase hanya jika memang disengaja."
    }

    Write-Host ''
    Write-Host 'Target migrasi yang dibaca dari .env:'
    Write-Host "  Koneksi : $connection"
    Write-Host "  Host    : $hostName"
    Write-Host "  Port    : $port"
    Write-Host "  Database: $databaseName"
    Write-Host 'Password tidak ditampilkan.'
    Write-Host 'Perintah berikut menjalankan migrate --seed. Script tidak pernah menjalankan migrate:fresh.'
    $confirmation = Read-Host "Ketik persis nama database '$databaseName' untuk melanjutkan"

    if ($confirmation -cne $databaseName) {
        throw 'Nama database tidak cocok. Migrasi dibatalkan.'
    }

    Write-Host 'Membuat tabel dan akun/data demo...'
    Invoke-CheckedCommand -Command 'php' -Arguments @('artisan', 'migrate', '--seed')

    Write-Host ''
    Write-Host 'Setup lokal selesai. Jalankan aplikasi dengan php artisan serve dan npm run dev.'
} finally {
    Pop-Location
}
