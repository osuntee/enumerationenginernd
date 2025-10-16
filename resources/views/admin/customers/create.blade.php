{{-- resources/views/customers/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create New Customer') }}
            </h2>
            <a href="{{ route('customers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Customers
            </a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('customers.store') }}">
                        @csrf

                        <!-- Customer Information Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                Customer Information
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Customer Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Customer Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Customer Address -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                        Address
                                    </label>
                                    <textarea id="address" 
                                              name="address" 
                                              rows="3" 
                                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                                    @error('address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Customer Status -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                            Active Customer
                                        </label>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Inactive customers cannot access their projects</p>
                                    @error('is_active')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Super Admin Staff Section -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                                Super Admin Staff Information
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                A super admin staff member will be created to manage this customer's projects and staff.
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Staff Name -->
                                <div>
                                    <label for="staff_name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Staff Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" 
                                           id="staff_name" 
                                           name="staff_name" 
                                           value="{{ old('staff_name') }}" 
                                           required 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('staff_name') border-red-500 @enderror">
                                    @error('staff_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Staff Email -->
                                <div>
                                    <label for="staff_email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input type="email" 
                                           id="staff_email" 
                                           name="staff_email" 
                                           value="{{ old('staff_email') }}" 
                                           required 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('staff_email') border-red-500 @enderror">
                                    @error('staff_email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Staff Phone -->
                                <div>
                                    <label for="staff_phone" class="block text-sm font-medium text-gray-700 mb-1">
                                        Phone Number
                                    </label>
                                    <input type="text" 
                                           id="staff_phone" 
                                           name="staff_phone" 
                                           value="{{ old('staff_phone') }}" 
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('staff_phone') border-red-500 @enderror">
                                    @error('staff_phone')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Blank space -->
                                <div></div>
                                
                                <!-- Staff Password -->
                                <div>
                                    <label for="staff_password" class="block text-sm font-medium text-gray-700 mb-1">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                           id="staff_password" 
                                           name="staff_password" 
                                           required 
                                           minlength="8"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('staff_password') border-red-500 @enderror">
                                    <p class="mt-1 text-xs text-gray-500">Minimum 8 characters</p>
                                    @error('staff_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label for="staff_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" 
                                           id="staff_password_confirmation" 
                                           name="staff_password_confirmation" 
                                           required 
                                           minlength="8"
                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>

                                <!-- Staff Status -->
                                <div class="md:col-span-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               id="staff_is_active" 
                                               name="staff_is_active" 
                                               value="1" 
                                               {{ old('staff_is_active', true) ? 'checked' : '' }}
                                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                        <label for="staff_is_active" class="ml-2 block text-sm text-gray-700">
                                            Active Staff Member
                                        </label>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">This staff member will have super admin privileges</p>
                                    @error('staff_is_active')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('customers.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Customer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('staff_password');
            const confirmPassword = document.getElementById('staff_password_confirmation');
            
            function validatePassword() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords don't match");
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('change', validatePassword);
            confirmPassword.addEventListener('keyup', validatePassword);
        });
    </script>
</x-app-layout>