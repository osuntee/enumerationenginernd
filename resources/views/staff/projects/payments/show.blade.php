<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $payment->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $payment->project->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.payments.edit', [$payment->project, $payment]) }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit Payment
                </a>
                <a href="{{ route('staff.projects.payments.index', $payment->project) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Payments
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Payment Details Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Details</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Amount</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">₦{{ number_format($payment->amount, 2) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Frequency</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $payment->frequency === 'weekly' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $payment->frequency === 'monthly' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $payment->frequency === 'yearly' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $payment->frequency === 'one_off' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ ucfirst(str_replace('_', ' ', $payment->frequency)) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Type</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $payment->payment_type === 'manual' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $payment->payment_type === 'gateway' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ $payment->payment_type === 'both' ? 'bg-teal-100 text-teal-800' : '' }}">
                                    {{ ucfirst($payment->payment_type) }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($payment->is_active && $payment->isValidForDate())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Active
                                    </span>
                                @elseif($payment->is_active && !$payment->isValidForDate())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Scheduled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Partial Payments</dt>
                            <dd class="mt-1">
                                @if($payment->allow_partial_payments)
                                    <span class="text-green-600 text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Allowed
                                    </span>
                                @else
                                    <span class="text-gray-500 text-sm">Not allowed</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Valid Period</dt>
                            <dd class="mt-1 text-sm text-gray-700">
                                @if($payment->start_date && $payment->end_date)
                                    {{ $payment->start_date->format('M d, Y') }} - {{ $payment->end_date->format('M d, Y') }}
                                @elseif($payment->start_date)
                                    From {{ $payment->start_date->format('M d, Y') }}
                                @elseif($payment->end_date)
                                    Until {{ $payment->end_date->format('M d, Y') }}
                                @else
                                    No restrictions
                                @endif
                            </dd>
                        </div>
                    </div>

                    @if($payment->description)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-700">{{ $payment->description }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Enumerations</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ number_format($stats['total_enumerations']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Paid</p>
                                <p class="text-2xl font-semibold text-green-900">{{ number_format($stats['paid_count']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending</p>
                                <p class="text-2xl font-semibold text-yellow-900">{{ number_format($stats['pending_count']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Overdue</p>
                                <p class="text-2xl font-semibold text-red-900">{{ number_format($stats['overdue_count']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Financial Summary</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <dt class="text-sm font-medium text-green-600">Total Collected</dt>
                            <dd class="mt-2 text-3xl font-bold text-green-900">₦{{ number_format($stats['total_collected'], 2) }}</dd>
                        </div>

                        <div class="text-center p-4 bg-red-50 rounded-lg">
                            <dt class="text-sm font-medium text-red-600">Outstanding Amount</dt>
                            <dd class="mt-2 text-3xl font-bold text-red-900">₦{{ number_format($stats['total_outstanding'], 2) }}</dd>
                        </div>

                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <dt class="text-sm font-medium text-blue-600">Collection Rate</dt>
                            <dd class="mt-2 text-3xl font-bold text-blue-900">
                                @php
                                    $total_expected = $stats['total_collected'] + $stats['total_outstanding'];
                                    $collection_rate = $total_expected > 0 ? ($stats['total_collected'] / $total_expected) * 100 : 0;
                                @endphp
                                {{ number_format($collection_rate, 1) }}%
                            </dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enumeration Payments Table -->
            @if($payment->enumerationPayments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Recent Enumeration Payments</h3>
                            <a href="{{ route('staff.projects.payments.enumerations.index', $payment) }}" class="text-blue-600 hover:text-blue-900 text-sm">View All</a>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Enumeration
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount Due
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Amount Paid
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Due Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($payment->enumerationPayments->take(10) as $enumerationPayment)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $enumerationPayment->enumeration->enumerationData->first()->value ?? 'N/A' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₦{{ number_format($enumerationPayment->amount_due, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                ₦{{ number_format($enumerationPayment->amount_paid, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $status = $enumerationPayment->amount_paid >= $enumerationPayment->amount_due ? 'paid' : 
                                                            ($enumerationPayment->due_date && $enumerationPayment->due_date->isPast() ? 'overdue' : ($enumerationPayment->amount_paid > 0 ? 'partial' :'pending'));
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                                                    {{ $status === 'partial' ? 'bg-blue-100 text-blue-800' : '' }}">
                                                    {{ $status === 'partial' ? 'Partially Paid' : ucfirst($status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $enumerationPayment->due_date ? $enumerationPayment->due_date->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('staff.projects.payments.enumerations.show', $enumerationPayment) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No enumeration payments</h3>
                        <p class="mt-1 text-sm text-gray-500">This payment configuration has no associated enumeration payments yet.</p>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Metadata</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-gray-900">{{ $payment->created_at->format('M d, Y \a\t g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-gray-900">{{ $payment->updated_at->format('M d, Y \a\t g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Payment ID</dt>
                            <dd class="mt-1 text-gray-900 font-mono">#{{ $payment->id }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Project ID</dt>
                            <dd class="mt-1 text-gray-900 font-mono">#{{ $payment->project_id }}</dd>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-staff>