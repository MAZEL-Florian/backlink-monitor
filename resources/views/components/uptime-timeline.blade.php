<div class="bg-white border border-gray-200 rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                Uptime timeline
            </h3>
            <p class="text-sm text-gray-500">Uptime percentage over time</p>
        </div>
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                <span class="text-lg font-semibold text-blue-600">{{ $uptimeData['uptime_percentage'] }}%</span>
            </div>
            <select class="text-sm border-gray-300 rounded-md" onchange="updateUptimePeriod(this.value)">
                <option value="7" {{ $uptimeData['period_days'] == 7 ? 'selected' : '' }}>7 days</option>
                <option value="30" {{ $uptimeData['period_days'] == 30 ? 'selected' : '' }}>30 days</option>
                <option value="90" {{ $uptimeData['period_days'] == 90 ? 'selected' : '' }}>90 days</option>
            </select>
        </div>
    </div>

    <div class="relative">
        <div class="flex items-center space-x-1 mb-2 overflow-x-auto pb-2 uptime-timeline" data-preserve="true">
            @foreach($uptimeData['data'] as $day)
                <div class="flex-shrink-0 group relative">
                    <div class="w-2 h-8 rounded-sm timeline-bar {{ $day['has_check'] ? ($day['is_active'] ? 'bg-emerald-500' : 'bg-rose-500') : 'bg-gray-200' }}"
                         title="{{ $day['date'] }}: {{ $day['has_check'] ? ($day['is_active'] ? 'Active' : 'Inactive') : 'No check' }}{{ $day['status_code'] ? ' (HTTP ' . $day['status_code'] . ')' : '' }}"
                         data-preserve="true">
                    </div>
                    
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        <div class="font-medium">{{ $day['date'] }}</div>
                        @if($day['has_check'])
                            <div class="text-{{ $day['is_active'] ? 'emerald' : 'rose' }}-300">
                                {{ $day['is_active'] ? '✓ Active' : '✗ Inactive' }}
                            </div>
                            @if($day['status_code'])
                                <div class="text-gray-300">HTTP {{ $day['status_code'] }}</div>
                            @endif
                        @else
                            <div class="text-gray-300">No check</div>
                        @endif
                        <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-900"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-between text-xs text-gray-500 mt-2">
            <span>{{ $uptimeData['start_date'] }}</span>
            <span>{{ $uptimeData['end_date'] }}</span>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200">
        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ $uptimeData['active_days'] }}</div>
            <div class="text-xs text-gray-500">Active days</div>
        </div>
        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ $uptimeData['total_days'] - $uptimeData['active_days'] }}</div>
            <div class="text-xs text-gray-500">Inactive days</div>
        </div>
        <div class="text-center">
            <div class="text-lg font-semibold text-gray-900">{{ $uptimeData['total_days'] }}</div>
            <div class="text-xs text-gray-500">Total checks</div>
        </div>
    </div>

    <div class="flex items-center justify-center space-x-6 mt-4 pt-4 border-t border-gray-200">
        <div class="flex items-center">
            <div class="w-3 h-3 bg-emerald-500 rounded-sm mr-2"></div>
            <span class="text-sm text-gray-600">Active</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-rose-500 rounded-sm mr-2"></div>
            <span class="text-sm text-gray-600">Inactive</span>
        </div>
        <div class="flex items-center">
            <div class="w-3 h-3 bg-gray-200 rounded-sm mr-2"></div>
            <span class="text-sm text-gray-600">No check</span>
        </div>
    </div>

    <div class="mt-4 text-center">
        <button onclick="window.location.reload()" class="text-sm text-blue-600 hover:text-blue-800">
            🔄 Actualiser les données
        </button>
    </div>
</div>

<script>
function updateUptimePeriod(days) {
    const url = new URL(window.location);
    url.searchParams.set('uptime_days', days);
    window.location.href = url.toString();
}
</script>
