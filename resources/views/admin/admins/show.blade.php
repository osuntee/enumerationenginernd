<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Details') }}
            </h2>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admins.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Admins
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Admin Profile Header -->
                    <div class="flex items-center mb-8">
                        <div class="flex-shrink-0 h-16 w-16">
                            <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-lg font-medium text-gray-700">
                                    {{ substr($admin->name, 0, 2) }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-6">
                            <h3 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                {{ $admin->name }}
                                @if($admin->id === Auth::id())
                                    <span class="text-sm text-blue-600 font-normal">(You)</span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-500">{{ $admin->email }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $admin->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                </span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $admin->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($admin->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Details -->
                    <div class="border-t border-gray-200 pt-6">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $admin->name }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $admin->email }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Role</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $admin->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $admin->role)) }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $admin->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($admin->status) }}
                                    </span>
                                </dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $admin->created_at->format('F j, Y \a\t g:i A') }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $admin->updated_at->format('F j, Y \a\t g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Action Buttons -->
                    <div class="border-t border-gray-200 pt-6 mt-6">
                        @if(Auth::user()->role === 'super_admin')
                            <div class="flex items-center justify-between">
                                <div class="flex space-x-3">
                                    <a href="{{ route('admins.edit', $admin) }}" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        Edit Admin
                                    </a>
                                    
                                    @if($admin->id !== Auth::id())
                                        @if($admin->status === 'active')
                                            <form method="POST" action="{{ route('admins.suspend', $admin) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm"
                                                        onclick="return confirm('Are you sure you want to suspend this admin?')">
                                                    Suspend Admin
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admins.activate', $admin) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                                    Activate Admin
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>

                                @if($admin->id !== Auth::id())
                                    <form method="POST" action="{{ route('admins.destroy', $admin) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm"
                                                onclick="return confirm('Are you sure you want to delete this admin? This action cannot be undone.')">
                                            Delete Admin
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>