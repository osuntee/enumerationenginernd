<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <nav class="flex mb-2" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-500 hover:text-blue-600">Projects</a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <a href="{{ route('projects.show', $project) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 md:ml-2">{{ $project->name }}</a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <a href="{{ route('projects.codes.index', $project) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-blue-600 md:ml-2">Batch Codes</a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 text-sm font-medium text-gray-800 md:ml-2">Batch #{{ $batch->number }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Batch Details: <span class="text-blue-600">#{{ $batch->number }}</span> ({{ $batch->code }})
                </h2>
            </div>

            <div class="flex flex-wrap gap-2">
                <button onclick="window.print()" class="bg-gray-800 hover:bg-black text-white font-bold py-2 px-4 rounded text-sm transition-colors flex items-center shadow-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Print QR Labels
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Batch Identifier</p>
                    <p class="text-lg font-mono font-bold text-gray-900">{{ $batch->code }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Codes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($batch->codes->count()) }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Used Codes</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($batch->codes->where('is_used', true)->count()) }}</p>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm text-center">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Unused Codes</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($batch->codes->where('is_used', false)->count()) }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-xl border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 uppercase tracking-wider text-sm">QR Code Inventory</h3>
                    <div class="text-xs text-gray-400 font-medium">Showing page {{ $codes->currentPage() }} of {{ $codes->lastPage() }}</div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        @foreach($codes as $code)
                            <div class="border {{ $code->is_used ? 'border-blue-100 bg-blue-50/30' : 'border-gray-200 bg-white' }} rounded-xl p-4 flex flex-col items-center transition-all hover:shadow-md relative group">
                                @if($code->is_used)
                                    <div class="absolute top-2 right-2">
                                        <span class="flex h-3 w-3 relative">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                        </span>
                                    </div>
                                @endif

                                <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100 mb-3 group-hover:scale-105 transition-transform">
                                    <img src="data:image/png;base64,{{ $code->qrcode }}" alt="QR Code" class="w-full aspect-square object-contain">
                                </div>
                                <p class="text-[10px] font-mono font-bold text-gray-400 mb-1 truncate w-full text-center">{{ $code->reference }}</p>
                                <span class="px-2 py-0.5 {{ $code->is_used ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }} rounded text-[9px] font-black uppercase tracking-tighter">
                                    {{ $code->is_used ? 'Used' : 'Available' }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $codes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style type="text/css">
        @media print {
            body * {
                visibility: hidden;
            }
            .py-12, .py-12 * {
                visibility: visible;
            }
            .py-12 {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                padding: 0 !important;
            }
            .grid-cols-1, .grid-cols-2, .grid-cols-3, .grid-cols-4, .grid-cols-5 {
                grid-template-columns: repeat(4, 1fr) !important;
            }
            .shadow-sm, .border, .bg-gray-50, .bg-blue-50\/30 {
                box-shadow: none !important;
                border: 1px solid #eee !important;
                background: white !important;
            }
            .pagination, .mb-8, .border-b {
                display: none !important;
            }
        }
    </style>
</x-app-layout>