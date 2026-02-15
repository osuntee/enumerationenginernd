<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Generate New Batch') }}: {{ $project->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-8">
                    <form method="POST" action="{{ route('staff.projects.codes.store', $project) }}">
                        @csrf

                        <div class="mb-8">
                            <div class="flex items-center mb-4">
                                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <h3 class="text-lg font-bold text-gray-900">Batch Configuration</h3>
                            </div>
                            <p class="text-gray-500 text-sm mb-6">Specify how many unique enumeration codes you want to generate in this batch. Each code will have a unique QR code for field use.</p>

                            <div class="space-y-4">
                                <div>
                                    <label for="count" class="block text-sm font-bold text-gray-700 mb-2">Number of Codes to Generate</label>
                                    <div class="relative">
                                        <input type="number" name="count" id="count" min="1" max="2000" value="100" required
                                            class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all font-medium text-gray-900"
                                            placeholder="Enter amount (e.g. 100)">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                            <span class="text-gray-400 text-sm font-bold">CODES</span>
                                        </div>
                                    </div>
                                    @error('count')
                                        <p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-xs text-gray-400 font-medium italic">* Maximum 2,000 codes per batch for optimal performance.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                            <a href="{{ route('staff.projects.codes.index', $project) }}" class="text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">
                                Cancel and Go Back
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-blue-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Generate Batch
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-8 bg-indigo-50 rounded-xl p-6 border border-indigo-100">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wider mb-1">What happens next?</h4>
                        <p class="text-sm text-indigo-700 leading-relaxed">
                            Once generated, you'll be able to download the entire batch as a PDF containing ready-to-print QR codes. These can be distributed to field staff for immediate enumeration without needing manual data entry for references.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-staff>