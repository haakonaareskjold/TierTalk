<div class="px-4 py-8" wire:poll.5s>
    @if($sessionEnded)
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">✅</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Session Ended</h1>
                <p class="text-gray-600 mb-6">Thank you for participating in this TierTalk session!</p>
                <a href="{{ route('home') }}" class="inline-block bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg transition">
                    Go Home
                </a>
            </div>
        </div>
    @elseif(!$participant)
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                <div class="text-6xl mb-4">🔒</div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Not Joined</h1>
                <p class="text-gray-600 mb-6">You need to join this session first.</p>
                <a href="{{ route('session.join', $session->slug) }}" class="inline-block bg-primary hover:bg-primary-dark text-white px-6 py-3 rounded-lg transition">
                    Join Session
                </a>
            </div>
        </div>
    @else
        <div class="max-w-2xl mx-auto">
            <!-- Header -->
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                <p class="text-gray-500 mt-1">Welcome, <span class="font-medium text-primary">{{ $participant->username }}</span>!</p>
            </div>

            <!-- Questions -->
            <div class="space-y-4">
                @forelse($questions as $question)
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $question->question_text }}</h3>

                        @if(in_array($question->id, $votedQuestionIds))
                            <div class="text-center py-2">
                                <div class="inline-flex items-center gap-2 bg-green-100 text-green-800 px-4 py-2 rounded-full mb-4">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Vote submitted!</span>
                                </div>

                                <!-- Live Results -->
                                @php
                                    $totalVotes = $question->votes->count();
                                @endphp
                                @if($totalVotes > 0)
                                    <div class="mt-4">
                                        <p class="text-sm text-gray-500 mb-3">Live Results ({{ $totalVotes }} vote{{ $totalVotes !== 1 ? 's' : '' }})</p>
                                        <div class="space-y-2">
                                            @foreach($question->answer_choices as $option)
                                                @php
                                                    $count = $question->votes->where('vote_value', $option)->count();
                                                    $percentage = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
                                                @endphp
                                                <div class="text-left">
                                                    <div class="flex justify-between text-sm mb-1">
                                                        <span class="text-gray-700">{{ $option }}</span>
                                                        <span class="text-gray-500">{{ $count }} ({{ $percentage }}%)</span>
                                                    </div>
                                                    <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
                                                        <div
                                                            class="h-full bg-primary transition-all duration-500"
                                                            style="width: {{ $percentage }}%"
                                                        ></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="flex flex-wrap justify-center gap-3">
                                @foreach($question->answer_choices as $option)
                                    <button
                                        wire:click="vote({{ $question->id }}, '{{ addslashes($option) }}')"
                                        class="px-6 py-3 rounded-xl border-2 border-gray-200 hover:border-primary hover:bg-primary hover:text-white text-gray-700 font-medium transition transform hover:scale-105"
                                    >
                                        {{ $option }}
                                    </button>
                                @endforeach
                            </div>
                            <p class="text-center text-gray-400 text-sm mt-3">Select your answer</p>
                        @endif
                    </div>
                @empty
                    <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
                        <div class="text-6xl mb-4">⏳</div>
                        <p class="text-gray-500">Waiting for questions from the host...</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>
