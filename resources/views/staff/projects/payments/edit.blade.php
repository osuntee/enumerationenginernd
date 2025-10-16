<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Payment') }}: {{ $payment->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.payments.show', [$project, $payment]) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    View Payment
                </a>
                <a href="{{ route('staff.projects.payments.index', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Payments
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('staff.projects.payments.update', [$project, $payment]) }}">
                @csrf
                @method('PUT')

                <!-- Payment Configuration -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="space-y-6">
                            <!-- Payment Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="name" id="name" value="{{ old('name', $payment->name) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., Monthly Rent, Registration Fee, Service Charge">
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Description
                                </label>
                                <textarea name="description" id="description" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Brief description of this payment">{{ old('description', $payment->description) }}</textarea>
                                <p class="text-sm text-gray-500 mt-1">Optional description to help identify this payment.</p>
                            </div>

                            <!-- Amount -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Amount (₦) <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₦</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" value="{{ old('amount', $payment->amount) }}" required
                                        class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                        placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>

                            <!-- Frequency -->
                            <div>
                                <label for="frequency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Frequency <span class="text-red-500">*</span>
                                </label>
                                <select name="frequency" id="frequency" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Frequency</option>
                                    <option value="one_off" {{ old('frequency', $payment->frequency) == 'one_off' ? 'selected' : '' }}>One-time Payment</option>
                                    <option value="weekly" {{ old('frequency', $payment->frequency) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="monthly" {{ old('frequency', $payment->frequency) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="yearly" {{ old('frequency', $payment->frequency) == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                <p class="text-sm text-gray-500 mt-1">How often this payment is due.</p>
                            </div>

                            <!-- Payment Type -->
                            <div>
                                <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Type <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_type" id="payment_type" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Payment Type</option>
                                    <option value="manual" {{ old('payment_type', $payment->payment_type) == 'manual' ? 'selected' : '' }}>Manual Payment Only</option>
                                    <option value="gateway" {{ old('payment_type', $payment->payment_type) == 'gateway' ? 'selected' : '' }}>Gateway Payment Only</option>
                                    <option value="both" {{ old('payment_type', $payment->payment_type) == 'both' ? 'selected' : '' }}>Both Manual & Gateway</option>
                                </select>
                                <p class="text-sm text-gray-500 mt-1">How payments can be processed.</p>
                            </div>

                            <!-- Date Range -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Start Date
                                    </label>
                                    <input type="date" name="start_date" id="start_date" 
                                        value="{{ old('start_date', $payment->start_date ? $payment->start_date->format('Y-m-d') : '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-sm text-gray-500 mt-1">When this payment becomes available.</p>
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        End Date
                                    </label>
                                    <input type="date" name="end_date" id="end_date" 
                                        value="{{ old('end_date', $payment->end_date ? $payment->end_date->format('Y-m-d') : '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <p class="text-sm text-gray-500 mt-1">When this payment expires (optional).</p>
                                </div>
                            </div>

                            <!-- Options -->
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" name="allow_partial_payments" id="allow_partial_payments" value="1"
                                        {{ old('allow_partial_payments', $payment->allow_partial_payments) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="allow_partial_payments" class="ml-2 block text-sm text-gray-900">
                                        Allow Partial Payments
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 ml-6">Allow users to pay less than the full amount.</p>

                                <div class="flex items-center">
                                    <input type="checkbox" name="is_active" id="is_active" value="1"
                                        {{ old('is_active', $payment->is_active) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                                        Active Payment
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500 ml-6">Enable this payment configuration.</p>
                            </div>

                            <!-- Payment Statistics (Read-only info) -->
                            <div class="bg-gray-50 rounded-lg p-4 border">
                                <h3 class="text-sm font-medium text-gray-900 mb-3">Payment Statistics</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-600">Total Collected:</span>
                                        <span class="font-medium text-green-600">₦{{ number_format($payment->getTotalCollected(), 2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Outstanding:</span>
                                        <span class="font-medium {{ $payment->getTotalOutstanding() > 0 ? 'text-red-600' : 'text-gray-600' }}">
                                            ₦{{ number_format($payment->getTotalOutstanding(), 2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Created:</span>
                                        <span class="text-gray-900">{{ $payment->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Last Modified:</span>
                                        <span class="text-gray-900">{{ $payment->updated_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="flex justify-between pt-6 border-t border-gray-200">
                                <div class="flex space-x-2">
                                    <a href="{{ route('staff.projects.payments.index', $project) }}" 
                                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                        Cancel
                                    </a>
                                    <a href="{{ route('staff.projects.payments.show', [$project, $payment]) }}" 
                                       class="bg-indigo-300 hover:bg-indigo-400 text-indigo-800 font-bold py-2 px-4 rounded">
                                        View Details
                                    </a>
                                </div>
                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    Update Payment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-set end date validation based on start date
        document.getElementById('start_date').addEventListener('change', function() {
            const startDate = this.value;
            const endDateInput = document.getElementById('end_date');
            if (startDate) {
                endDateInput.min = startDate;
            }
        });

        // Auto-set start date validation based on end date
        document.getElementById('end_date').addEventListener('change', function() {
            const endDate = this.value;
            const startDateInput = document.getElementById('start_date');
            if (endDate) {
                startDateInput.max = endDate;
            }
        });

        // Initialize date constraints on page load
        document.addEventListener('DOMContentLoaded', function() {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            
            if (startDate) {
                document.getElementById('end_date').min = startDate;
            }
            if (endDate) {
                document.getElementById('start_date').max = endDate;
            }
        });
    </script>
</x-staff>