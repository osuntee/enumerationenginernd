<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Enumeration Details') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $project->name }} - ID: {{ $enumeration->id }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.enumeration.edit', $enumeration) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit
                </a>
                <form method="POST" action="{{ route('staff.projects.enumeration.toggleVerification', $enumeration) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="{{ $enumeration->is_verified ? 'bg-yellow-500 hover:bg-yellow-700' : 'bg-green-500 hover:bg-green-700' }} text-white font-bold py-2 px-4 rounded">
                        {{ $enumeration->is_verified ? 'Mark as Unverified' : 'Mark as Verified' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('staff.projects.enumeration.destroy', $enumeration) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this enumeration?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Delete
                    </button>
                </form>
                <a href="{{ route('staff.projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Enumeration Metadata -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Enumeration Information</h3>
                        <div class="flex items-center space-x-2">
                            @if($enumeration->is_verified)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    Pending Verification
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Enumerated By</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if($enumeration->staff)
                                    {{ $enumeration->staff->name }}
                                    @if($enumeration->staff->position)
                                        <span class="text-gray-500">({{ $enumeration->staff->position }})</span>
                                    @endif
                                @else
                                    <span class="text-gray-500 italic">Not assigned</span>
                                @endif
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Enumeration Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $enumeration->enumerated_at ? $enumeration->enumerated_at->format('M d, Y g:i A') : 'Not specified' }}
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $enumeration->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    </div>
                    
                    @if($enumeration->notes)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded-md">{{ $enumeration->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Field Data -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">Field Data</h3>
                    
                    @php
                        $fieldValues = $enumeration->getFieldValues();
                    @endphp
                    
                    <div class="space-y-6">
                        @forelse($project->projectFields as $field)
                            @php
                                $value = $fieldValues[$field->name] ?? null;
                            @endphp
                            
                            <div class="border-b border-gray-200 pb-6 last:border-b-0">
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">
                                        {{ $field->label }}
                                        @if($field->required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                                        @if($value !== null && $value !== '')
                                            @switch($field->type)
                                                @case('textarea')
                                                    <div class="bg-gray-50 p-3 rounded-md whitespace-pre-wrap">{{ $value }}</div>
                                                    @break
                                                    
                                                @case('email')
                                                    <a href="mailto:{{ $value }}" class="text-blue-600 hover:text-blue-800">{{ $value }}</a>
                                                    @break
                                                    
                                                @case('url')
                                                    <a href="{{ $value }}" target="_blank" class="text-blue-600 hover:text-blue-800">{{ $value }}</a>
                                                    @break
                                                    
                                                @case('tel')
                                                    <a href="tel:{{ $value }}" class="text-blue-600 hover:text-blue-800">{{ $value }}</a>
                                                    @break
                                                    
                                                @case('date')
                                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y') }}
                                                    @break
                                                    
                                                @case('time')
                                                    {{ \Carbon\Carbon::parse($value)->format('g:i A') }}
                                                    @break
                                                    
                                                @case('datetime-local')
                                                    {{ \Carbon\Carbon::parse($value)->format('M d, Y g:i A') }}
                                                    @break
                                                    
                                                @case('color')
                                                    <div class="flex items-center">
                                                        <div class="w-6 h-6 rounded border border-gray-300 mr-2" style="background-color: {{ $value }}"></div>
                                                        <span>{{ $value }}</span>
                                                    </div>
                                                    @break
                                                    
                                                @case('checkbox')
                                                    @if($value == '1' || $value === true)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Yes
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            No
                                                        </span>
                                                    @endif
                                                    @break
                                                    
                                                @case('checkboxes')
                                                    @if(is_array($value) && count($value) > 0)
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach($value as $item)
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                                    {{ $item }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-gray-500 italic">None selected</span>
                                                    @endif
                                                    @break
                                                    
                                                @case('file')
                                                    @if($value)
                                                        <a href="{{ $value }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800">
                                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            View File
                                                        </a>
                                                    @else
                                                        <span class="text-gray-500 italic">No file uploaded</span>
                                                    @endif
                                                    @break
                                                    
                                                @default
                                                    {{ $value }}
                                            @endswitch
                                        @else
                                            <span class="text-gray-500 italic">No data</span>
                                        @endif
                                        
                                        @if($field->help_text)
                                            <p class="text-xs text-gray-400 mt-1">{{ $field->help_text }}</p>
                                        @endif
                                    </dd>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No fields configured</h3>
                                <p class="mt-1 text-sm text-gray-500">This project doesn't have any fields configured yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <!-- QR Code -->
            @if($enumeration->qrcode)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code</h3>
                        <img src="data:image/png;base64,{{ $enumeration->qrcode }}" 
                            alt="QR Code for {{ $enumeration->reference }}"
                            class="border rounded shadow w-48 h-48 mx-auto">
                        
                        <p class="text-sm text-gray-600 mt-2 text-center">
                            Reference: {{ $enumeration->reference }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-staff>