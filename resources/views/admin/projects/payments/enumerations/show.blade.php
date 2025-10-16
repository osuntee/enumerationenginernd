<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Enumeration Payment Details
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $enumerationPayment->enumeration->project->name ?? 'N/A' }} - {{ $enumerationPayment->projectPayment->name }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @php
                    $outstanding = $enumerationPayment->amount_due - $enumerationPayment->amount_paid;
                @endphp
                @if($outstanding > 0 && ($enumerationPayment->projectPayment->payment_type === 'both' || $enumerationPayment->projectPayment->payment_type === 'manual'))
                    <button command="show-modal" commandfor="recordPaymentDialog"
                       class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Record Payment
                    </button>
                @endif
                <a href="{{ route('projects.payments.enumerations.index', $enumerationPayment->projectPayment) }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to List
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Payment Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Overview</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="mx-auto h-8 w-8 p-5 flex items-center justify-center rounded-full bg-gray-100 text-blue-600 text-3xl font-bold">
                                        ₦
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-blue-600">Amount Due</p>
                                    <p class="text-2xl font-bold text-blue-900">₦{{ number_format($enumerationPayment->amount_due, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-green-600">Amount Paid</p>
                                    <p class="text-2xl font-bold text-green-900">₦{{ number_format($enumerationPayment->amount_paid, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-{{ $outstanding > 0 ? 'red' : 'gray' }}-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-{{ $outstanding > 0 ? 'red' : 'gray' }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-{{ $outstanding > 0 ? 'red' : 'gray' }}-600">Outstanding</p>
                                    <p class="text-2xl font-bold text-{{ $outstanding > 0 ? 'red' : 'gray' }}-900">₦{{ number_format($outstanding, 2) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-purple-600">Progress</p>
                                    <p class="text-2xl font-bold text-purple-900">
                                        {{ $enumerationPayment->amount_due > 0 ? number_format(($enumerationPayment->amount_paid / $enumerationPayment->amount_due) * 100, 1) : 0 }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="flex justify-between text-sm text-gray-600 mb-2">
                            <span>Payment Progress</span>
                            <span>{{ $enumerationPayment->amount_due > 0 ? number_format(($enumerationPayment->amount_paid / $enumerationPayment->amount_due) * 100, 1) : 0 }}% Complete</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $enumerationPayment->amount_due > 0 ? min(($enumerationPayment->amount_paid / $enumerationPayment->amount_due) * 100, 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Enumeration Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Enumeration Details</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">{{ $enumerationPayment->enumeration->enumerationData->first()->projectField->label ?? 'N/A' }}</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $enumerationPayment->enumeration->enumerationData->first()->value ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Project</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $enumerationPayment->projectPayment->project->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $enumerationPayment->projectPayment->project->customer->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Created Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $enumerationPayment->enumeration->created_at ? $enumerationPayment->enumeration->created_at->format('M d, Y g:i A') : 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Payment Configuration -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment Configuration</h3>
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Payment Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $enumerationPayment->projectPayment->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Frequency</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucwords(str_replace('_', ' ', $enumerationPayment->projectPayment->frequency)) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $enumerationPayment->due_date ? $enumerationPayment->due_date->format('M d, Y') : 'No due date' }}
                                    @if($enumerationPayment->due_date && $enumerationPayment->due_date->isPast() && $outstanding > 0)
                                        <span class="ml-2 text-red-600 text-xs">({{ $enumerationPayment->due_date->diffForHumans() }})</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    @php
                                        $status = $enumerationPayment->amount_paid >= $enumerationPayment->amount_due ? 'paid' : 
                                                ($enumerationPayment->due_date && $enumerationPayment->due_date->isPast() ? 'overdue' : 'pending');
                                        if ($enumerationPayment->amount_paid > 0 && $outstanding > 0) {
                                            $status = 'partial';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $status === 'partial' ? 'bg-blue-100 text-blue-800' : '' }}">
                                        {{ $status === 'partial' ? 'Partially Paid' : ucfirst($status) }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Payment Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
                        @if($outstanding > 0 && ($enumerationPayment->projectPayment->payment_type === 'both' || $enumerationPayment->projectPayment->payment_type === 'manual'))
                            <button command="show-modal" commandfor="recordPaymentDialog"
                               class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Record Payment
                            </button>
                        @endif
                    </div>

                    @if($enumerationPayment->paymentTransactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recorded By</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($enumerationPayment->paymentTransactions->sortByDesc('created_at') as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->created_at->format('M d, Y g:i A') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                                                ₦{{ number_format($transaction->amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucwords(str_replace('_', ' ', $transaction->payment_source ?? 'Manual')) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ ucwords(str_replace('_', ' ', $transaction->payment_method ?? 'Bank Transfer')) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $transaction->reference ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $transaction->status === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $transaction->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $transaction->status === 'failed' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ ucfirst($transaction->status ?? 'success') }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                                {{ $transaction->recordedBy ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- No payments yet -->
                        <div class="text-center py-8">
                            <span class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-gray-100 text-gray-400 text-3xl font-bold">
                                ₦
                            </span>
                            <h4 class="mt-2 text-sm font-medium text-gray-900">No payments recorded</h4>
                            <p class="mt-1 text-sm text-gray-500">No payment transactions have been recorded for this enumeration payment yet.</p>
                            @if($outstanding > 0 && ($enumerationPayment->projectPayment->payment_type === 'both' || $enumerationPayment->projectPayment->payment_type === 'manual'))
                                <div class="mt-6">
                                    <button command="show-modal" commandfor="recordPaymentDialog" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-500 hover:bg-green-700">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Record First Payment
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($enumerationPayment->enumeration)
                <!-- Enumeration Data Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Enumeration Data</h3>
                            <a href="#" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                View Full Details →
                            </a>
                        </div>
                        
                        @if($enumerationPayment->enumeration->enumerationData->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($enumerationPayment->enumeration->enumerationData->take(6) as $data)
                                    <div class="border border-gray-200 rounded-lg p-3">
                                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $data->projectField->label ?? 'Unknown Field' }}
                                        </dt>
                                        <dd class="mt-1 text-sm text-gray-900 break-words">
                                            @if(is_array($data->value))
                                                {{ implode(', ', $data->value) }}
                                            @else
                                                {{ $data->value ?? 'N/A' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                                
                                @if($enumerationPayment->enumeration->enumerationData->count() > 6)
                                    <div class="border border-gray-200 rounded-lg p-3 flex items-center justify-center bg-gray-50">
                                        <span class="text-sm text-gray-500">
                                            +{{ $enumerationPayment->enumeration->enumerationData->count() - 6 }} more fields
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No enumeration data available.</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Record Payment Modal -->
    <el-dialog>
        <dialog id="recordPaymentDialog" aria-labelledby="modal-title" class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
            <el-dialog-backdrop class="fixed inset-0 bg-gray-900/50 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>
            <div tabindex="0" class="flex min-h-full items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
                <el-dialog-panel class="relative transform overflow-hidden text-left transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 sm:w-full sm:max-w-2xl data-closed:sm:translate-y-0 data-closed:sm:scale-95">
                    <form id="recordPaymentForm" action="{{ route('projects.payments.enumerations.record-payment', $enumerationPayment) }}" method="POST">
                        @csrf
                        <div class="bg-white p-5">
                            <!-- Header -->
                            <div class="flex items-center mb-6">
                                <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 text-xl font-bold">₦</span>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Record Payment Transaction
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Outstanding Balance: ₦{{ number_format($outstanding, 2) }}
                                    </p>
                                </div>
                            </div>

                            <!-- Form Fields -->
                            <div class="space-y-6">
                                <!-- Payment Amount -->
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Payment Amount <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">₦</span>
                                        </div>
                                        <input type="number" 
                                            id="amount" 
                                            name="amount" 
                                            step="0.01" 
                                            min="0.01" 
                                            max="{{ $outstanding }}"
                                            class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                            placeholder="0.00" 
                                            required>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">Maximum amount: ₦{{ number_format($outstanding, 2) }}</p>
                                </div>

                                <!-- Quick Amount Buttons -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quick Select</label>
                                    <div class="flex flex-wrap gap-2">
                                        @php
                                            $quickAmounts = [];
                                            if ($outstanding >= 1000) $quickAmounts[] = 1000;
                                            if ($outstanding >= 5000) $quickAmounts[] = 5000;
                                            if ($outstanding >= 10000) $quickAmounts[] = 10000;
                                            $quickAmounts[] = $outstanding; // Full amount
                                        @endphp
                                        
                                        @foreach($quickAmounts as $quickAmount)
                                            <button type="button" 
                                                    onclick="setAmount({{ $quickAmount }})" 
                                                    class="px-3 py-1 text-sm {{ $quickAmount == $outstanding ? 'bg-green-100 hover:bg-green-200 text-green-700 border-green-300' : 'bg-gray-100 hover:bg-gray-200 text-gray-700' }} rounded-md border">
                                                {{ $quickAmount == $outstanding ? 'Full Amount (' : '' }}₦{{ number_format($quickAmount, 0) }}{{ $quickAmount == $outstanding ? ')' : '' }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                        Payment Method <span class="text-red-500">*</span>
                                    </label>
                                    <select id="payment_method" 
                                            name="payment_method" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                            required>
                                        <option value="">Select payment method</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="mobile_money">Mobile Money</option>
                                        <option value="card">Card Payment</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <!-- Reference Number -->
                                <div>
                                    <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">
                                        Reference Number
                                    </label>
                                    <input type="text" 
                                        id="reference" 
                                        name="reference" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                        placeholder="Enter transaction reference (optional)">
                                    <p class="mt-1 text-xs text-gray-500">Transaction ID, cheque number, or other reference</p>
                                </div>

                                <!-- Transaction Date -->
                                <div>
                                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 mb-2">
                                        Transaction Date <span class="text-red-500">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                        id="transaction_date" 
                                        name="transaction_date" 
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                        required>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                        Notes
                                    </label>
                                    <textarea id="notes" 
                                            name="notes" 
                                            rows="3" 
                                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                                            placeholder="Add any additional notes about this payment..."></textarea>
                                </div>

                                <!-- Payment Summary -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Payment Summary</h4>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Current Balance:</span>
                                            <span class="font-medium">₦{{ number_format($outstanding, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Payment Amount:</span>
                                            <span class="font-medium text-green-600" id="summaryAmount">₦0.00</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Remaining Balance:</span>
                                            <span class="font-medium" id="remainingBalance">₦{{ number_format($outstanding, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Status After Payment:</span>
                                            <span class="font-medium" id="newStatus">
                                                @php
                                                    $currentStatus = $enumerationPayment->amount_paid >= $enumerationPayment->amount_due ? 'Paid' : 
                                                            ($enumerationPayment->due_date && $enumerationPayment->due_date->isPast() ? 'Overdue' : 'Pending');
                                                    if ($enumerationPayment->amount_paid > 0 && $outstanding > 0) {
                                                        $currentStatus = 'Partially Paid';
                                                    }
                                                @endphp
                                                {{ $currentStatus }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="bg-gray-50 flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3 p-5">
                            <button type="button" 
                                    command="close"
                                    commandfor="recordPaymentDialog" 
                                    class="sm:w-auto h-11 inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 sm:text-sm">
                                Cancel
                            </button>
                            <button type="submit" 
                                    id="recordButton"
                                    class="sm:w-auto h-11 inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:text-sm">
                                Record Payment
                            </button>
                        </div>
                    </form>
                </el-dialog-panel>
            </div>
        </dialog>
    </el-dialog>

    <!-- Include the script tag for @tailwindplus/elements -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set current date/time as default
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('transaction_date').value = now.toISOString().slice(0, 16);
            
            // Update payment summary when amount changes
            document.getElementById('amount').addEventListener('input', updateSummary);
            
            // Initialize summary
            updateSummary();
        });

        // Set amount from quick select buttons
        function setAmount(amount) {
            document.getElementById('amount').value = amount.toFixed(2);
            updateSummary();
        }

        function updateSummary() {
            const amountInput = document.getElementById('amount');
            const amount = parseFloat(amountInput.value) || 0;
            const currentBalance = {{ $outstanding }};
            
            // Update summary display
            document.getElementById('summaryAmount').textContent = '₦' + amount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            const remaining = Math.max(0, currentBalance - amount);
            document.getElementById('remainingBalance').textContent = '₦' + remaining.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            // Update status
            const statusElement = document.getElementById('newStatus');
            if (remaining === 0) {
                statusElement.textContent = 'Paid';
                statusElement.className = 'font-medium text-green-600';
            } else if (amount > 0) {
                statusElement.textContent = 'Partially Paid';
                statusElement.className = 'font-medium text-blue-600';
            } else {
                statusElement.textContent = '{{ $currentStatus }}';
                statusElement.className = 'font-medium text-gray-600';
            }
        }

        // Validate amount input
        document.getElementById('amount').addEventListener('blur', function() {
            const amount = parseFloat(this.value);
            const maxAmount = {{ $outstanding }};
            
            if (amount > maxAmount) {
                this.setCustomValidity(`Amount cannot exceed ₦${maxAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`);
                this.reportValidity();
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</x-app-layout>