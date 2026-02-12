<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Project Batches') }}: {{ $project->name }}
                </h2>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('projects.codes.create', $project) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                    Generate New Batch
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
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 font-bold text-gray-900">Batch {{ $batch->number }}</td>
                                            <td class="px-6 py-4 text-gray-600 font-mono text-sm">{{ $batch->code }}</td>
                                            <td class="px-6 py-4 text-center text-gray-900 font-medium">{{ number_format($batch->codes_count) }}</td>
                                            <td class="px-6 py-4 text-center">
                                                <span class="px-2 py-1 {{ $batch->used_codes_count > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-50 text-gray-500' }} rounded-full text-xs font-bold">
                                                    {{ number_format($batch->used_codes_count) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                @php
                                                    $percentage = $batch->codes_count > 0 ? ($batch->used_codes_count / $batch->codes_count) * 100 : 0;
                                                @endphp
                                                <div class="w-full bg-gray-100 rounded-full h-2 max-w-[100px] mx-auto">
                                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                                </div>
                                                <div class="text-[10px] text-center mt-1 text-gray-400 font-bold uppercase">{{ round($percentage) }}%</div>
                                            </td>
                                            <td class="px-6 py-4 text-gray-500 text-sm">
                                                {{ $batch->created_at->format('M d, Y') }}
                                                <div class="text-[10px] text-gray-400">{{ $batch->created_at->format('h:i A') }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('projects.codes.show', [$project, $batch]) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                                        View Codes
                                                    </a>
                                                    <a href="#" class="inline-flex items-center px-3 py-1.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                                        Print PDF
                                                    </a>
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
                            <a href="{{ route('projects.codes.create', $project) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-bold text-sm text-white uppercase tracking-widest hover:bg-blue-700 transition ease-in-out duration-150">
                                Create Your First Batch
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>