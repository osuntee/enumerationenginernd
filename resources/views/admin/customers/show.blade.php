{{-- resources/views/customers/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Customer Details: {{ $customer->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('customers.edit', $customer) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Edit Customer
                </a>
                
                @if($customer->is_active)
                    <form method="POST" action="{{ route('customers.toggleStatus', $customer) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm"
                                onclick="return confirm('Are you sure you want to deactivate this customer?')">
                            Deactivate
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('customers.toggleStatus', $customer) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                            Activate
                        </button>
                    </form>
                @endif

                <a href="{{ route('customers.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Back to List
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

            <!-- Customer Information Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Name</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Address</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        @if($customer->address)
                                            {{ $customer->address }}
                                        @else
                                            <span class="text-gray-400">Not provided</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Statistics</h3>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $stats['total_projects'] }}</div>
                                    <div class="text-sm text-green-600">Total Projects</div>
                                    @if($stats['active_projects'] > 0)
                                        <div class="text-xs text-gray-600 mt-1">{{ $stats['active_projects'] }} active</div>
                                    @endif
                                </div>

                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $stats['total_staff'] }}</div>
                                    <div class="text-sm text-blue-600">Total Staff</div>
                                    @if($stats['active_staff'] > 0)
                                        <div class="text-xs text-gray-600 mt-1">{{ $stats['active_staff'] }} active</div>
                                    @endif
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-sm text-gray-600">Created</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->created_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $customer->created_at->diffForHumans() }}</div>
                                </div>

                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="text-sm text-gray-600">Last Updated</div>
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->updated_at->format('M j, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $customer->updated_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Projects Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Projects ({{ $customer->projects->count() }})</h3>
                            <span class="text-sm text-gray-500">Showing latest projects</span>
                        </div>
                        <a href="{{ route('customers.projects.index', $customer) }}" class="text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>

                    @if($customer->projects->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Count</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($customer->projects->sortByDesc('created_at')->take(5) as $project)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $project->name ?? 'Untitled Project' }}</div>
                                                @if(isset($project->description))
                                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ $project->description }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $project->staff_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ ($project->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ($project->is_active ?? true) ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $project->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                @if(Route::has('projects.show'))
                                                    <a href="{{ route('projects.show', $project) }}" 
                                                    class="text-indigo-600 hover:text-indigo-900">View</a>
                                                @else
                                                    <span class="text-gray-400">View</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">No Projects</h4>
                            <p class="text-gray-600">This customer doesn't have any projects yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Staff Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Staff Members ({{ $customer->staff->count() }})</h3>
                            <span class="text-sm text-gray-500">Showing latest staff members</span>
                        </div>
                        <a href="{{ route('customers.staff.index', $customer) }}" class="text-indigo-600 hover:text-indigo-900">View All</a>
                    </div>

                    @if($customer->staff->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projects</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($customer->staff->sortByDesc('created_at')->take(5) as $staffMember)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8">
                                                        <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                            <span class="text-xs font-medium text-gray-700">
                                                                {{ strtoupper(substr($staffMember->name ?? 'U', 0, 2)) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">{{ $staffMember->name ?? 'Unknown' }}</div>
                                                        @if(isset($staffMember->email))
                                                            <div class="text-xs text-gray-500">{{ $staffMember->email }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucfirst($staffMember->staff_type ?? 'N/A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $staffMember->projects_count ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ ($staffMember->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ($staffMember->is_active ?? true) ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $staffMember->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('customers.staff.show', $staffMember) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">No Staff</h4>
                            <p class="text-gray-600">This customer doesn't have any staff members assigned yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>