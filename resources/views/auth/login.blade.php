<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PT Agra Kreasi Widyatama</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-xl font-bold text-center text-gray-800">PT Agra Kreasi Widyatama</h1>
        <p class="text-xs text-center text-gray-500 mb-6">Production Planning & Cutting Control System</p>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold mb-1">Email</label>
                <input type="email" name="email" required autofocus placeholder="manajer@agrakreasi.com" class="w-full border border-gray-300 p-2.5 rounded">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Password</label>
                <input type="password" name="password" required placeholder="password" class="w-full border border-gray-300 p-2.5 rounded">
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded">
                Masuk ke Sistem
            </button>
        </form>
    </div>
</body>
</html>