<div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Résultat du Diagnostic</h3>

    @php
        $data = $this->getData();
    @endphp

    @if(!empty($data))
        <div class="space-y-4">
            <!-- Code Résultat -->
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                <span class="text-sm font-medium text-gray-700">Code Orientation</span>
                <code class="text-lg font-bold text-blue-600">{{ $data['result_code'] ?? 'N/A' }}</code>
            </div>

            <!-- Label Résultat -->
            <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg border border-green-200">
                <span class="text-sm font-medium text-gray-700">Domaine Principal</span>
                <span class="text-lg font-semibold text-green-700">{{ $data['main_domain'] ?? 'Non disponible' }}</span>
            </div>

            <!-- Nombre de Domaines -->
            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                <span class="text-sm font-medium text-gray-700">Domaines Recommandés</span>
                <span class="text-2xl font-bold text-blue-700">{{ $data['domain_count'] ?? 0 }}</span>
            </div>

            <!-- Liste des Domaines -->
            @if(!empty($data['domains']))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-3">Domaines Compatibles :</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($data['domains'] as $domain)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $domain }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    @else
    <p class="text-gray-500 text-center py-4">Aucun résultat disponible</p>
    @endif
</div>
