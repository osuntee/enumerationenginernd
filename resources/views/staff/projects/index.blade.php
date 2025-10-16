<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm sm:w-auto text-center">
                    Create New Project
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
                    @if($projects->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($projects as $project)
                                <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                    <p class="text-gray-600 text-xs mb-2">{{ $project->customer->name }}</p>
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h3>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $project->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $project->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                    
                                    @if($project->description)
                                        <p class="text-gray-600 text-sm mb-4">{{ Str::limit($project->description, 100) }}</p>
                                    @endif
                                    
                                    <!-- Project Statistics -->
                                    <div class="grid grid-cols-3 gap-3 text-sm text-gray-500 mb-4">
                                        <div class="text-center">
                                            <div class="font-medium text-gray-900">{{ $project->project_fields_count }}</div>
                                            <div class="text-xs">Fields</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium text-gray-900">{{ $project->staff_count }}</div>
                                            <div class="text-xs">Staff</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium text-gray-900">{{ $project->enumerations_count }}</div>
                                            <div class="text-xs">Records</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="space-y-2">
                                        <!-- Primary Actions Row -->
                                        <div class="flex space-x-2">
                                            <a href="{{ route('staff.projects.show', $project) }}" class="flex-1 text-center bg-blue-500 hover:bg-blue-700 text-white py-2 px-3 rounded text-sm font-medium">
                                                View Project
                                            </a>
                                            <a href="{{ route('staff.projects.staff.index', $project) }}" class="flex-1 text-center bg-indigo-500 hover:bg-indigo-700 text-white py-2 px-3 rounded text-sm font-medium">
                                                Manage Staff
                                            </a>
                                        </div>
                                        
                                        <!-- Secondary Actions Row -->
                                        <div class="flex space-x-2">
                                            <a href="{{ route('staff.projects.edit', $project) }}" class="flex-1 text-center bg-gray-500 hover:bg-gray-700 text-white py-2 px-3 rounded text-sm">
                                                Edit
                                            </a>
                                            @if($project->is_active)
                                                <form method="POST" action="{{ route('staff.projects.deactivate', $project) }}" class="flex-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-700 text-white py-2 px-3 rounded text-sm">
                                                        Deactivate
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('staff.projects.activate', $project) }}" class="flex-1">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white py-2 px-3 rounded text-sm">
                                                        Activate
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No projects yet</h3>
                            <p class="text-gray-600 mb-4">Get started by creating your first project to manage enumerations and staff.</p>
                            <a href="{{ route('staff.projects.create') }}" class="inline-flex items-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Project
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-staff>