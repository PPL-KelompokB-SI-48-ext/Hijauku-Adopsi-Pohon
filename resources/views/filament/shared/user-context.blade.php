@php($user = filament()->auth()->user())

@if ($user)
    <div class="hijauku-user-context" aria-label="Akun yang sedang masuk">
        <span class="hijauku-user-context__name">{{ filament()->getUserName($user) }}</span>
        <span class="hijauku-user-context__role">{{ $roleLabel }}</span>
    </div>
@endif
