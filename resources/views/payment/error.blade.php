<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Error</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full text-center">
            <!-- Error Icon -->
            <div class="mb-6">
                <div class="text-6xl mb-4">❌</div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Payment Error</h1>
            </div>

            <!-- Error Message -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-800">{{ $message }}</p>
            </div>

            <!-- Help Text -->
            <div class="text-gray-600 mb-6">
                <p class="mb-2">If you have questions about your invoice or payment, please contact us.</p>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="mailto:thesunshinespotwvl@gmail.com" class="block bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 rounded-lg transition">
                    Contact Support
                </a>
            </div>
        </div>
    </div>
</body>
</html>
