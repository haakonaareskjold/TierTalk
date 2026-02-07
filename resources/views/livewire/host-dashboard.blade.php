<div class="space-y-6" wire:poll.5s>
    <!-- Session Info Header -->
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $session->title }}</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Expires: {{ $session->expires_at->diffForHumans() }}
                    @if($session->isExpired())
                        <span class="text-red-500 font-medium">(Expired)</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $participants->count() }}/{{ $session->max_participants }} Participants
                </span>
                <button
                    wire:click="endSession"
                    wire:confirm="Are you sure you want to end this session?"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition"
                >
                    End Session
                </button>
            </div>
        </div>

        <!-- Share URL -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 mb-2">Share this link with participants:</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    value="{{ $session->share_url }}"
                    readonly
                    class="flex-1 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-600"
                    id="shareUrl"
                >
                <button
                    onclick="navigator.clipboard.writeText(document.getElementById('shareUrl').value); this.innerText='Copied!'; setTimeout(() => this.innerText='Copy', 2000)"
                    class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg transition"
                >
                    Copy
                </button>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Questions Panel -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Questions</h2>

                <!-- Add New Question -->
                <form wire:submit="addQuestion" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="mb-3">
                        <input
                            type="text"
                            wire:model="newQuestion"
                            placeholder="Add a new question..."
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                        >
                        @error('newQuestion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Answer Options -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Answer Options</label>
                        <div class="space-y-2">
                            @foreach($newOptions as $oIndex => $option)
                                <div class="flex gap-2 items-center">
                                    <input
                                        type="text"
                                        wire:model="newOptions.{{ $oIndex }}"
                                        placeholder="e.g., Yes, No, Messi..."
                                        class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                    >
                                    @if(count($newOptions) > 2)
                                        <button
                                            type="button"
                                            wire:click="removeNewOption({{ $oIndex }})"
                                            class="px-2 py-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition"
                                        >
                                            ✕
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <button
                            type="button"
                            wire:click="addNewOption"
                            class="mt-2 text-xs text-primary hover:text-primary-dark font-medium"
                        >
                            + Add option
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg transition"
                    >
                        Add Question
                    </button>
                </form>

                <!-- Questions List -->
                <div class="space-y-4">
                    @forelse($questions as $question)
                        <div class="border border-gray-200 rounded-xl p-4 {{ !$question->is_active ? 'opacity-50' : '' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ $question->question_text }}</p>
                                    <div class="mt-2 flex items-center gap-4 text-sm text-gray-500">
                                        <span>{{ $question->votes->count() }} votes</span>
                                        @if(!$question->is_active)
                                            <span class="text-orange-500">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        wire:click="toggleQuestion({{ $question->id }})"
                                        class="text-gray-500 hover:text-gray-700 px-2 py-1 rounded transition"
                                        title="{{ $question->is_active ? 'Deactivate' : 'Activate' }}"
                                    >
                                        {{ $question->is_active ? '👁️' : '👁️‍🗨️' }}
                                    </button>
                                    <button
                                        wire:click="resetQuestion({{ $question->id }})"
                                        wire:confirm="Reset all votes for this question?"
                                        class="text-orange-500 hover:text-orange-700 px-2 py-1 rounded transition"
                                        title="Reset votes"
                                    >
                                        🔄
                                    </button>
                                    <button
                                        wire:click="deleteQuestion({{ $question->id }})"
                                        wire:confirm="Delete this question?"
                                        class="text-red-500 hover:text-red-700 px-2 py-1 rounded transition"
                                        title="Delete"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <!-- Answer Options Display -->
                            <div class="mt-2 text-xs text-gray-500">
                                Options: {{ implode(', ', $question->answer_choices) }}
                            </div>

                            <!-- Host Voting -->
                            @php
                                $hostVoted = $hostParticipant && $question->hasVoteFrom($hostParticipant);
                            @endphp
                            @if(!$hostVoted)
                                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <p class="text-sm text-blue-700 mb-2">Cast your vote:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($question->answer_choices as $option)
                                            <button
                                                wire:click="vote({{ $question->id }}, '{{ addslashes($option) }}')"
                                                class="px-4 py-2 text-sm rounded-lg border-2 border-blue-300 hover:border-blue-500 hover:bg-blue-500 hover:text-white text-blue-700 font-medium transition"
                                            >
                                                {{ $option }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 text-sm text-green-600">✓ You voted: {{ $question->getVoteFrom($hostParticipant)?->vote_value }}</div>
                            @endif

                            <!-- Vote Distribution -->
                            @if($question->votes->count() > 0)
                                <div class="mt-4">
                                    <div class="flex flex-wrap items-end gap-2">
                                        @foreach($question->answer_choices as $option)
                                            @php
                                                $count = $question->votes->where('vote_value', $option)->count();
                                                $percentage = $question->votes->count() > 0 ? ($count / $question->votes->count()) * 100 : 0;
                                            @endphp
                                            <div class="flex-1 min-w-16">
                                                <div class="text-center text-xs text-gray-500 mb-1 truncate" title="{{ $option }}">{{ Str::limit($option, 10) }}</div>
                                                <div class="h-12 bg-gray-100 rounded relative overflow-hidden">
                                                    <div
                                                        class="absolute bottom-0 left-0 right-0 bg-primary transition-all duration-300"
                                                        style="height: {{ $percentage }}%"
                                                    ></div>
                                                </div>
                                                <div class="text-center text-xs text-gray-600 mt-1">{{ $count }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-center py-8">No questions yet. Add your first question above!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Participants Panel -->
        <div class="bg-white rounded-2xl shadow-lg p-6 h-fit">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Participants</h2>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($participants as $participant)
                    <div class="flex items-center justify-between gap-3 p-2 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-medium">
                                {{ strtoupper(substr($participant->username, 0, 1)) }}
                            </div>
                            <span class="text-gray-700">{{ $participant->username }}</span>
                        </div>
                        <button
                            wire:click="kickParticipant({{ $participant->id }})"
                            wire:confirm="Kick {{ $participant->username }}? This will remove them and their votes."
                            class="text-red-400 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded transition"
                            title="Kick participant"
                        >
                            🚫
                        </button>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Waiting for participants...</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
