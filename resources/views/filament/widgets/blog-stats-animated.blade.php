@php /** @var array $stats */ @endphp
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach ($stats as $stat)
        <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center relative overflow-hidden">
            <div class="absolute inset-0 pointer-events-none">
                <!-- Animated SVG Wave -->
                <svg class="absolute bottom-0 left-0 w-full h-16 animate-wave" viewBox="0 0 1440 320" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#6366f1" fill-opacity="0.2" d="M0,160L48,170.7C96,181,192,203,288,202.7C384,203,480,181,576,154.7C672,128,768,96,864,117.3C960,139,1056,213,1152,229.3C1248,245,1344,203,1392,181.3L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>
            <div class="relative z-10 flex flex-col items-center">
                <div class="text-4xl font-bold text-indigo-600 animate-pulse">{{ $stat['value'] }}</div>
                <div class="mt-2 text-lg font-semibold text-gray-700">{{ __($stat['label']) }}</div>
                <div class="mt-1 text-sm text-gray-400">{{ __($stat['description'] ?? '') }}</div>
            </div>
        </div>
    @endforeach
</div>
<style>
@keyframes wave {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50px); }
}
.animate-wave {
    animation: wave 2s infinite linear alternate;
}
</style>