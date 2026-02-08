<x-layouts.app title="TierTalk - Real-time Voting Sessions">
    <div class="px-4 py-12">
        <div class="max-w-3xl mx-auto text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Welcome to TierTalk 🗣️
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
                Create real-time voting sessions and gather instant feedback from your audience.
            </p>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 mb-8">
                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-100 mb-6">How it works</h2>

                <div class="grid md:grid-cols-3 gap-6 text-left">
                    <div class="p-4">
                        <div class="text-3xl mb-3">1️⃣</div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Create a Session</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Set up your questions, participant limit, and session duration.</p>
                    </div>

                    <div class="p-4">
                        <div class="text-3xl mb-3">2️⃣</div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Share the Link</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">Participants join with just a username - no signup required.</p>
                    </div>

                    <div class="p-4">
                        <div class="text-3xl mb-3">3️⃣</div>
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Collect Votes</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">See real-time results as participants cast their votes.</p>
                    </div>
                </div>
            </div>

            <a href="{{ route('session.create') }}" class="inline-block bg-primary hover:bg-primary-dark text-white text-lg font-semibold px-8 py-4 rounded-xl transition transform hover:scale-105">
                Start a TierTalk Session
            </a>
        </div>
    </div>
</x-layouts.app>
