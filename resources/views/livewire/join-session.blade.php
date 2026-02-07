<div class="px-4 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
            @if($sessionExpired)
                <div class="text-6xl mb-4">⏰</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Session Expired</h1>
                <p class="text-gray-600 mb-6">This TierTalk session has ended.</p>
                <a href="{{ route('home') }}" class="inline-block bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg transition">
                    Go Home
                </a>
            @elseif($sessionFull)
                <div class="text-6xl mb-4">🚫</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Session Full</h1>
                <p class="text-gray-600 mb-6">This session has reached its maximum number of participants.</p>
                <a href="{{ route('home') }}" class="inline-block bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg transition">
                    Go Home
                </a>
            @else
                <div class="text-6xl mb-4">🗣️</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $session->title }}</h1>
                <p class="text-gray-600 mb-6">Enter your name to join this session and start voting!</p>

                <form wire:submit="join" class="space-y-4">
                    <div>
                        <input
                            type="text"
                            wire:model="username"
                            placeholder="Your name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-center text-lg"
                            autofocus
                        >
                        @error('username') <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg transition"
                    >
                        Join Session
                    </button>
                </form>

                <p class="text-gray-400 text-sm mt-4">
                    {{ $session->participants()->count() }}/{{ $session->max_participants }} participants joined
                </p>
            @endif
        </div>
    </div>
</div>
