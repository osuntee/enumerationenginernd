<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Enumeration;
use App\Models\EnumerationPayment;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function index($ref)
    {
        $enumeration = Enumeration::where('reference', $ref)->firstOrFail();
        $enumerationPayments = EnumerationPayment::where('enumeration_id', $enumeration->id)
            ->with('paymentTransactions')
            ->get();


        $project = $enumeration->project;
        $enumeration->load(['enumerationData.projectField']);
        $project->load(['projectFields' => function ($query) {
            $query->active()->ordered();
        }]);

        return view('front.index', compact('enumeration', 'project', 'enumerationPayments'));
    }

    /**
     * Display the specified enumeration payment details.
     */
    public function viewPayment(EnumerationPayment $enumerationPayment)
    {
        // Load all necessary relationships
        $enumerationPayment->load([
            'projectPayment.project.customer',
            'enumeration.enumerationData.projectField',
            'paymentTransactions' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        $gateways = Gateway::where('is_active', true)->get();

        return view('front.payment', compact('enumerationPayment', 'gateways'));
    }

    /**
     * Process a new payment transaction for the specified enumeration payment.
     */
    public function processPayment(Request $request, EnumerationPayment $enumerationPayment)
    {
        $outstanding = $enumerationPayment->amount_due - $enumerationPayment->amount_paid;

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $outstanding,
            'payment_gateway' => 'required|exists:gateways,id',
        ]);

        $gateway = Gateway::find($request->input('payment_gateway'));

        if (!$gateway || !$gateway->is_active) {
            return back()->withErrors(['payment_gateway' => 'Selected payment gateway is not available.'])->withInput();
        }

        // Use first two letters of the gateway name as prefix (e.g. PA, FL)
        $prefix = strtoupper(substr($gateway->name, 0, 2));
        $reference = $prefix . '_' . now()->format('YmdHis') . strtoupper(Str::random(6));

        switch (strtolower($gateway->name)) {
            case 'paystack':
                return $this->initializePaystackPayment($gateway, $request, $enumerationPayment, $reference);

            case 'flutterwave':
                return $this->initializeFlutterwavePayment($gateway, $request, $enumerationPayment, $reference);

            default:
                return back()->with('error', 'Unsupported payment gateway selected.');
        }
    }



    private function initializePaystackPayment($gateway, $request, $enumerationPayment, $reference)
    {
        $response = Http::withToken($gateway->secret_key)->post('https://api.paystack.co/transaction/initialize', [
            'email' => 'agundugodsent@gmail.com',
            'amount' => $request->amount * 100,
            'reference' => $reference,
            'callback_url' => route('front.paystack.callback'),
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['data']['authorization_url'])) {
            PaymentTransaction::create([
                'enumeration_payment_id' => $enumerationPayment->id,
                'gateway_transaction_id' => $gateway->id,
                'amount' => $request->amount,
                'type' => 'payment',
                'status' => 'pending',
                'payment_method' => $gateway->name,
                'reference' => $reference,
                'payment_source' => 'gateway',
                'transaction_date' => now(),
            ]);

            return redirect()->away($data['data']['authorization_url']);
        }

        return back()->with('error', 'Unable to initiate Paystack payment.');
    }

    private function initializeFlutterwavePayment($gateway, $request, $enumerationPayment, $reference)
    {
        $response = Http::withToken($gateway->secret_key)->post('https://api.flutterwave.com/v3/payments', [
            'tx_ref' => $reference,
            'amount' => $request->amount,
            'currency' => 'NGN',
            'redirect_url' => route('front.flutterwave.callback'),
            'customer' => [
                'email' => 'agundugodsent@gmail.com',
                'name' => 'Agundu Godsent',
            ],
            'customizations' => [
                'title' => 'Enumeration Payment',
                'description' => 'Payment for enumeration service',
                'logo' => asset('images/logo.png'),
            ],
        ]);

        $data = $response->json();

        if ($response->successful() && isset($data['data']['link'])) {
            PaymentTransaction::create([
                'enumeration_payment_id' => $enumerationPayment->id,
                'gateway_transaction_id' => $gateway->id,
                'amount' => $request->amount,
                'type' => 'payment',
                'status' => 'pending',
                'payment_method' => $gateway->name,
                'reference' => $reference,
                'payment_source' => 'gateway',
                'transaction_date' => now(),
            ]);

            return redirect()->away($data['data']['link']);
        }

        return back()->with('error', 'Unable to initiate Flutterwave payment.');
    }

    public function handlePaystackCallback(Request $request)
    {
        $reference = $request->query('reference');

        $transaction = PaymentTransaction::where('reference', $reference)->first();
        if (!$transaction) {
            return redirect()->route('front.index')->with('error', 'Transaction not found.');
        }

        $gateway = Gateway::find($transaction->gateway_transaction_id);

        if (!$gateway || !$gateway->is_active) {
            return back()->withErrors(['payment_gateway' => 'Selected payment gateway is not available.'])->withInput();
        }

        $response = Http::withToken($gateway->secret_key)->get("https://api.paystack.co/transaction/verify/{$reference}");

        $data = $response->json();

        $enumerationPayment = $transaction->enumerationPayment;

        if ($data['status'] && $data['data']['status'] === 'success') {
            $transaction->update([
                'status' => 'success',
            ]);

            // Update the enumeration payment
            $enumerationPayment->update([
                'amount_paid' => $enumerationPayment->amount_paid + ($data['data']['amount'] / 100),
            ]);
        } else {
            $transaction->update([
                'status' => 'failed',
            ]);
        }

        return redirect()->route('front.payments.show', $enumerationPayment)->with('success', 'Payment updated successful.');
    }

    public function handleFlutterwaveCallback(Request $request)
    {
        // Flutterwave returns transaction_id and tx_ref
        $transactionId = $request->query('transaction_id');
        $txRef = $request->query('tx_ref');

        // Find the transaction in your DB using tx_ref (your generated reference)
        $transaction = PaymentTransaction::where('reference', $txRef)->first();
        if (!$transaction) {
            return redirect()->route('front.index')->with('error', 'Transaction not found.');
        }

        $gateway = Gateway::find($transaction->gateway_transaction_id);

        if (!$gateway || !$gateway->is_active) {
            return back()->withErrors(['payment_gateway' => 'Selected payment gateway is not available.'])->withInput();
        }

        // Verify the transaction with Flutterwave API
        $verifyUrl = "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify";

        $response = Http::withToken($gateway->secret_key)->get($verifyUrl);
        $data = $response->json();

        $enumerationPayment = $transaction->enumerationPayment;

        if (
            $response->successful() &&
            isset($data['data']['status']) &&
            $data['data']['status'] === 'successful'
        ) {
            $amountPaid = $data['data']['amount'];

            // Update transaction status
            $transaction->update([
                'status' => 'success',
            ]);

            // Update enumeration payment total
            $enumerationPayment->update([
                'amount_paid' => $enumerationPayment->amount_paid + $amountPaid,
            ]);

            return redirect()
                ->route('front.payments.show', $enumerationPayment)
                ->with('success', 'Payment was successful.');
        } else {
            $transaction->update([
                'status' => 'failed',
            ]);

            return redirect()
                ->route('front.payments.show', $enumerationPayment)
                ->with('error', 'Payment failed or was cancelled.');
        }
    }
}
