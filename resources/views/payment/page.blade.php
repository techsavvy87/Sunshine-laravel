<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        #payment-element {
            padding: 16px;
            border: 1px solid #d1d5db;
            border-radius: 14px;
            background: #ffffff;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .payment-card {
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            padding: 28px;
            background: #ffffff;
        }

        .field-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
        }

        .field-group input,
        .field-group select {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            background: #f8fafc;
            color: #0f172a;
        }

        .field-group input:focus,
        .field-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
            background: #ffffff;
        }

        .stripe-payment-card {
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .payment-icon-row {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            justify-content: flex-end;
        }

        .payment-icon-row img {
            width: 42px;
            height: auto;
            object-fit: contain;
        }

        .spinner-dot {
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255, 255, 255, 0.28);
            border-top-color: #ffffff;
            border-radius: 9999px;
            animation: spin 0.75s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6">
        <div class="w-full lg:w-1/2">
            <section class="payment-card shadow-sm">

                <form id="payment-form" class="mt-8 space-y-6">
                    @csrf
                    <input type="hidden" id="payment-token" value="{{ $paymentLink->secure_token }}">
                    <input type="hidden" id="payment-client-secret" value="">

                    <div class="space-y-6">
                        <div class="field-group">
                            <label class="mb-3 block text-sm font-semibold text-slate-700">Card details</label>
                            <div id="payment-element" class="stripe-payment-card"></div>
                            <div id="payment-errors" class="mt-3 text-sm text-red-600"></div>
                        </div>
                    </div>

                    <button id="submit-button" type="submit" class="inline-flex items-center justify-center w-full rounded-2xl bg-slate-900 px-5 py-4 text-base font-semibold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-400">
                        <span id="button-text">Pay ${{ number_format($paymentLink->amount, 2) }}</span>
                        <span id="spinner" class="hidden ml-3 inline-flex items-center gap-3">
                            <span class="spinner-dot"></span>
                            Processing
                        </span>
                    </button>
                </form>

                <div id="payment-message" class="hidden">
                    <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-6 text-emerald-900">
                        <div class="flex items-center gap-3">
                            <div class="flex-none h-11 w-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl">✓</div>
                            <div>
                                <h3 class="text-lg font-semibold">Payment completed</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const stripe = Stripe('{{ $stripePublicKey }}');
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const paymentErrors = document.getElementById('payment-errors');
        const paymentMessage = document.getElementById('payment-message');
        const token = document.getElementById('payment-token').value;
        let paymentElement;
        let elements;

        async function initializePayment() {
            submitButton.disabled = true;

            try {
                const response = await fetch('{{ route('payment.create-intent') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    },
                    body: JSON.stringify({ token: token }),
                });

                const data = await response.json();

                if (!data.status) {
                    paymentErrors.textContent = data.message || 'Unable to initialize payment.';
                    return;
                }

                const clientSecret = data.clientSecret;
                document.getElementById('payment-client-secret').value = clientSecret;

                elements = stripe.elements({
                    clientSecret,
                    appearance: {
                        theme: 'stripe',
                        labels: 'floating',
                        variables: {
                            fontFamily: 'Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
                            colorBackground: '#ffffff',
                            colorPrimaryText: '#0f172a',
                            colorText: '#0f172a',
                            colorBorder: '#d1d5db',
                        },
                    },
                });
                paymentElement = elements.create('payment', { paymentMethodOrder: ['card'] });
                paymentElement.mount('#payment-element');
                submitButton.disabled = false;
            } catch (error) {
                paymentErrors.textContent = 'Unable to initialize payment: ' + error.message;
            }
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            submitButton.disabled = true;
            paymentErrors.textContent = '';
            paymentMessage.classList.add('hidden');
            document.getElementById('button-text').classList.add('hidden');
            document.getElementById('spinner').classList.remove('hidden');

            try {
                const { error, paymentIntent } = await stripe.confirmPayment({
                    elements,
                    confirmParams: {
                        return_url: window.location.href,
                    },
                    redirect: 'if_required',
                });

                if (error) {
                    paymentErrors.textContent = error.message;
                    throw error;
                }

                if (paymentIntent && paymentIntent.status === 'succeeded') {
                    const confirmResponse = await fetch('{{ route('payment.confirm') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            token: token,
                            paymentIntentId: paymentIntent.id,
                        }),
                    });

                    const confirmData = await confirmResponse.json();

                    if (confirmData.status) {
                        paymentMessage.classList.remove('hidden');
                        form.classList.add('hidden');
                        const customerSummary = document.getElementById('customer-summary');
                        if (customerSummary) {
                            customerSummary.classList.add('hidden');
                        }
                    } else {
                        paymentErrors.textContent = confirmData.message || 'Payment confirmation failed.';
                        submitButton.disabled = false;
                    }
                } else {
                    paymentErrors.textContent = 'Payment could not be completed. Please try again.';
                    submitButton.disabled = false;
                }
            } catch (err) {
                submitButton.disabled = false;
            } finally {
                document.getElementById('button-text').classList.remove('hidden');
                document.getElementById('spinner').classList.add('hidden');
            }
        });

        initializePayment();
    </script>
</body>
</html>
