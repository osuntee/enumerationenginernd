<x-staff>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Staff Dashboard') }}: {{ $customer->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Projects -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-blue-50 text-blue-600 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Projects</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProjects) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Active Projects -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-green-50 text-green-600 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Projects</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($activeProjectsCount) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Staff -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Staff Count</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalStaff) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Enumerations -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-lg bg-purple-50 text-purple-600 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Submissions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalEnumerations) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Charts Section -->
                <div class="lg:col-span-2 space-y-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Recent Submissions Activity</h3>
                        <div class="h-64 relative">
                            <canvas id="staffEnumerationChart"></canvas>
                        </div>
                    </div>

                    <!-- Top Projects Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                        <div class="p-6 border-b border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900">Project Performance</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase font-bold">
                                    <tr>
                                        <th class="px-6 py-4">Project</th>
                                        <th class="px-6 py-4 text-center">Submissions</th>
                                        <th class="px-6 py-4 text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($topProjects as $project)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $project->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $project->code }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-bold">
                                                {{ $project->enumerations_count }} Records
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('staff.projects.show', $project) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">View Project</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Actions -->
                <div class="space-y-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Quick Actions</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <a href="{{ route('staff.projects.create') }}" class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-blue-50 hover:text-blue-700 transition-all group">
                                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-4 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                </div>
                                <span class="font-bold">New Project</span>
                            </a>
                            <a href="{{ route('staff.staff.index') }}" class="flex items-center p-4 bg-gray-50 rounded-xl hover:bg-indigo-50 hover:text-indigo-700 transition-all group">
                                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-4 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM9 9a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </div>
                                <span class="font-bold">Manage Staff</span>
                            </a>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Customer Details</h3>
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Organization</p>
                                <p class="text-sm font-bold text-gray-900">{{ $customer->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status</p>
                                <span class="px-2 py-0.5 {{ $customer->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }} rounded text-xs font-bold">
                                    {{ $customer->is_active ? 'Active' : 'Suspended' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('staffEnumerationChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartData['dates']) !!},
                    datasets: [{
                        label: 'Submissions',
                        data: {!! json_encode($chartData['counts']) !!},
                        backgroundColor: '#6366f1',
                        borderRadius: 8,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            color: '#f3f4f6'
                        },
                        ticks: {
                            maxTicksLimit: 6,
                            precision: 0
                        }
                    },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-staff>
