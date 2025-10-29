<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Enumeration Management') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if($project->is_active)
                    <a href="{{ route('projects.enumeration.create', $project) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Add New Record
                    </a>
                @endif
                @if($enumerations->count() > 0)
                    <a href="{{ route('projects.enumeration.export', $project) }}?format=csv" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Export CSV
                    </a>
                    <a href="{{ route('projects.enumeration.export', $project) }}?format=json" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Export JSON
                    </a>
                @endif
                <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($enumerations->count() > 0)
                        <!-- Summary Stats -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-green-600">Total Records</p>
                                        <p class="text-2xl font-semibold text-green-900">{{ $enumerations->total() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-blue-600">Verified Records</p>
                                        <p class="text-2xl font-semibold text-blue-900">{{ $enumerations->where('is_verified', true)->count() }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-yellow-600">Pending Verification</p>
                                        <p class="text-2xl font-semibold text-yellow-900">{{ $enumerations->where('is_verified', false)->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Enumeration Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Longitude
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Latitude
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            By
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Verified
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($enumerations as $enumeration)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $enumeration->enumerated_at->format('M d, Y') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $enumeration->enumerated_at->format('h:i A') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $enumeration->longitude ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $enumeration->latitude ?? 'N/A' }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    @if($enumeration->staff)
                                                        <div class="flex-shrink-0 h-8 w-8">
                                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                                <span class="text-xs font-medium text-gray-700">
                                                                    {{ substr($enumeration->staff->name, 0, 2) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $enumeration->staff->name }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="text-sm text-gray-500 italic">
                                                            Self Enumerated
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <form method="POST" action="{{ route('projects.enumeration.toggleVerification', $enumeration) }}" style="display: inline;">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enumeration->is_verified ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                        @if($enumeration->is_verified)
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Verified
                                                        @else
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                                            </svg>
                                                            Pending
                                                        @endif
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                                <a href="{{ route('projects.enumeration.show', $enumeration) }}" class="text-blue-600 hover:text-blue-900">
                                                    View
                                                </a>
                                                <a href="{{ route('projects.enumeration.edit', $enumeration) }}" class="text-indigo-600 hover:text-indigo-900">
                                                    Edit
                                                </a>
                                                <form method="POST" action="{{ route('projects.enumeration.destroy', $enumeration) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this enumeration record?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $enumerations->links() }}
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No enumeration records</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by adding your first enumeration record to this project.</p>
                            @if($project->is_active)
                                <div class="mt-6">
                                    <a href="{{ route('projects.enumeration.create', $project) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add First Record
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>