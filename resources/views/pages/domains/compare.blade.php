<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comparer les domaines - OrientationTech</title>
    @include('layouts.favicon')
    @include('layouts.theme-init')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
@include('layouts.navbar')
<main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
    <h1 class="text-3xl font-extrabold">Comparer les domaines</h1>
    <form method="GET" action="{{ route('domains.compare') }}" class="mt-6 rounded-2xl bg-white p-5 shadow-sm dark:bg-gray-800">
        <select name="domains[]" multiple class="min-h-40 w-full rounded-xl border p-4 dark:bg-gray-900">
            @foreach($allDomains as $item)<option value="{{ $item->id }}" @selected($domains->contains('id', $item->id))>{{ $item->name }}</option>@endforeach
        </select>
        <p class="mt-2 text-sm text-gray-500">Choisis 2 ou 3 domaines avec Ctrl/Cmd.</p>
        <button class="mt-4 rounded-xl bg-[#2563eb] px-5 py-2 font-bold text-white">Comparer</button>
    </form>
    @if($domains->count())
        <div class="mt-8 overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-white/10">
            <table class="min-w-full text-sm">
                <tbody>
                @foreach(['name'=>'Domaine','short_description'=>'Description','difficulty_level'=>'Difficulte','future_potential'=>'Potentiel','ai_impact'=>'Impact IA','junior_salary_max'=>'Salaire junior max','senior_salary_max'=>'Salaire senior max','category'=>'Categorie'] as $field => $label)
                    <tr class="border-b dark:border-white/10"><th class="bg-gray-50 p-4 text-left dark:bg-gray-900">{{ $label }}</th>@foreach($domains as $domain)<td class="p-4">{{ is_numeric($domain->$field) ? number_format($domain->$field) : $domain->$field }}</td>@endforeach</tr>
                @endforeach
                <tr><th class="bg-gray-50 p-4 text-left dark:bg-gray-900">Metiers</th>@foreach($domains as $domain)<td class="p-4">{{ implode(', ', (array)$domain->related_jobs) }}</td>@endforeach</tr>
                </tbody>
            </table>
        </div>
    @endif
</main>
@include('layouts.footer')
</body></html>
