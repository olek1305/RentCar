<x-layout>
    <div class="max-w-md mx-auto mt-10 bg-white p-6 shadow">
        <h1 class="text-xl font-bold mb-4">Logowanie</h1>

        @if ($errors->any())
            <div class="text-red-600 mb-3">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label for="email">Email</label>
                <input name="email" type="email" class="w-full border rounded px-3 py-2" required>
            </div>
            <div class="mb-4">
                <label for="password">Hasło</label>
                <input name="password" type="password" class="w-full border rounded px-3 py-2" required>
            </div>
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Zaloguj się</button>
        </form>
    </div>
</x-layout>
