<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Project Batches') }}: {{ $project->name }}
                </h2>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.codes.create', $project) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                    Generate New Batch
                </a>
                <a href="{{ route('staff.projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6">
                    @if($batches->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase font-bold tracking-wider">
                                        <th class="px-6 py-4 border-b">Batch #</th>
                                        <th class="px-6 py-4 border-b">Batch Identifier</th>
                                        <th class="px-6 py-4 border-b text-center">Total Codes</th>
                                        <th class="px-6 py-4 border-b text-center">Used</th>
                                        <th class="px-6 py-4 border-b text-center">Progress</th>
                                        <th class="px-6 py-4 border-b">Created At</th>
                                        <th class="px-6 py-4 border-b text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($batches as $batch)
                                        <tr class="hover:bg-gray-50 transition-colors" 
                                            x-data="{ 
                                                status: '{{ $batch->status }}',
                                                total: {{ $batch->status === 'completed' ? $batch->codes_count : $batch->total_codes }},
                                                used: {{ $batch->used_codes_count }},
                                                pollStatus() {
                                                    if (this.status === 'pending' || this.status === 'processing') {
                                                        fetch('{{ route('staff.projects.codes.status', [$project, $batch]) }}')
                                                            .then(response => response.json())
                                                            .then(data => {
                                                                this.status = data.status;
                                                                this.total = data.total_codes || data.codes_count;
                                                                if (data.is_completed) {
                                                                    // Optionally reload after a short delay to ensure counts are fully sync'd
                                                                    // but for now we just update the status
                                                                }
                                                            });
                                                    }
                                                }
                                            }"
                                            x-init="if (status === 'pending' || status === 'processing') { 
                                                setInterval(() => pollStatus(), 3000); 
                                            }">
                                            <td class="px-6 py-4 font-bold text-gray-900">Batch {{ $batch->number }}</td>
                                            <td class="px-6 py-4 text-gray-600 font-mono text-sm">
                                                {{ $batch->code }}
                                                
                                                <template x-if="status === 'pending'">
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800">
                                                        <svg class="animate-spin -ml-0.5 mr-1 h-2 w-2 text-yellow-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        Pending
                                                    </span>
                                                </template>
                                                
                                                <template x-if="status === 'processing'">
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800">
                                                        <svg class="animate-spin -ml-0.5 mr-1 h-2 w-2 text-blue-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                        Processing
                                                    </span>
                                                </template>
                                                
                                                <template x-if="status === 'failed'">
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800">
                                                        Failed
                                                    </span>
                                                </template>
                                            </td>
                                            <td class="px-6 py-4 text-center text-gray-900 font-medium" x-text="total.toLocaleString()">
                                                {{ number_format($batch->status === 'completed' ? $batch->codes_count : $batch->total_codes) }}
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2 py-1 {{ $batch->used_codes_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-50 text-gray-500' }} rounded-full text-xs font-bold" x-text="used.toLocaleString()">
                                                    {{ number_format($batch->used_codes_count) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="w-full bg-gray-100 rounded-full h-2 max-w-[100px] mx-auto">
                                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" :style="`width: ${total > 0 ? (used / total) * 100 : 0}%`" style="width: {{ ($batch->status === 'completed' ? $batch->codes_count : $batch->total_codes) > 0 ? ($batch->used_codes_count / ($batch->status === 'completed' ? $batch->codes_count : $batch->total_codes)) * 100 : 0 }}%"></div>
                                                </div>
                                                <div class="text-[10px] text-center mt-1 text-gray-400 font-bold uppercase" x-text="`${Math.round(total > 0 ? (used / total) * 100 : 0)}%`">{{ round((($batch->status === 'completed' ? $batch->codes_count : $batch->total_codes) > 0 ? ($batch->used_codes_count / ($batch->status === 'completed' ? $batch->codes_count : $batch->total_codes)) * 100 : 0)) }}%</div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-500 text-sm">
                                                {{ $batch->created_at->format('M d, Y') }}
                                                <div class="text-[10px] text-gray-400">{{ $batch->created_at->format('h:i A') }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <div x-show="status === 'completed'" class="flex gap-2">
                                                        <a href="{{ route('staff.projects.codes.show', [$project, $batch]) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                            View Codes
                                                        </a>
                                                        <a href="{{ route('staff.projects.codes.show', ['project' => $project, 'batch' => $batch, 'print' => 1]) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                            Print PDF
                                                        </a>
                                                    </div>
                                                    <div x-show="status !== 'completed' && status !== 'failed'">
                                                        <span class="inline-flex items-center px-3 py-1.5 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-400 uppercase tracking-widest cursor-not-allowed">
                                                            Processing...
                                                        </span>
                                                    </div>
                                                    <div x-show="status === 'failed'">
                                                        <span class="inline-flex items-center px-3 py-1.5 bg-red-50 border border-red-200 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest">
                                                            Failed
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 text-blue-600 rounded-full mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">No batches generated yet</h3>
                            <p class="text-gray-500 mb-6 max-w-sm mx-auto">Generate a new batch of pre-printed codes for field enumeration.</p>
                            <a href="{{ route('staff.projects.codes.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                Create Your First Batch
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-staff>
