<div class="space-y-6">
    <!-- Session Info Header -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $session->title }}</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    Expires: {{ $session->expires_at->diffForHumans() }}
                    @if($session->isExpired())
                        <span class="text-red-500 font-medium">(Expired)</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-3 py-1 rounded-full text-sm font-medium">
                    {{ $participants->count() }}/{{ $session->max_participants }} Participants
                </span>
                <button
                    wire:click="confirmEndSession"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition"
                >
                    End Session
                </button>
            </div>
        </div>

        <!-- Share URL -->
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Share this link with participants:</label>
            <div class="flex gap-2">
                <input
                    type="text"
                    value="{{ $session->share_url }}"
                    readonly
                    class="flex-1 px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-600 dark:text-gray-300"
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

        <!-- Display Settings -->
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <label class="block text-sm font-medium text-blue-700 dark:text-blue-300 mb-3">Display Settings for Participants</label>
            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:click="toggleShowAverageToAll"
                        {{ $session->show_average_to_all ? 'checked' : '' }}
                        class="w-4 h-4 text-primary border-gray-300 dark:border-gray-600 rounded focus:ring-primary"
                    >
                    <span class="text-sm text-gray-700 dark:text-gray-300">Show average to all participants</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:click="toggleShowHoverToAll"
                        {{ $session->show_hover_to_all ? 'checked' : '' }}
                        class="w-4 h-4 text-primary border-gray-300 dark:border-gray-600 rounded focus:ring-primary"
                    >
                    <span class="text-sm text-gray-700 dark:text-gray-300">Show voter names on hover to all</span>
                </label>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Questions Panel -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Questions</h2>

                <!-- Add New Question -->
                <form wire:submit="addQuestion" class="mb-6 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="mb-3">
                        <input
                            type="text"
                            wire:model="newQuestion"
                            placeholder="Add a new question..."
                            class="w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent placeholder-gray-400"
                        >
                        @error('newQuestion') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Answer Options -->
                    <div class="mb-3">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Answer Options</label>
                        <div class="space-y-2">
                            @foreach($newOptions as $oIndex => $option)
                                <div class="flex gap-2 items-center">
                                    <input
                                        type="text"
                                        wire:model="newOptions.{{ $oIndex }}"
                                        placeholder="e.g., Yes, No, Messi..."
                                        class="flex-1 px-3 py-1.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent placeholder-gray-400"
                                    >
                                    @if(count($newOptions) > 2)
                                        <button
                                            type="button"
                                            wire:click="removeNewOption({{ $oIndex }})"
                                            class="px-2 py-1 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
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
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 {{ !$question->is_active ? 'opacity-50' : '' }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $question->question_text }}</p>
                                    <div class="mt-2 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>{{ $question->votes->count() }} votes</span>
                                        @if(!$question->is_active)
                                            <span class="text-orange-500">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button
                                        wire:click="confirmToggle({{ $question->id }})"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 px-2 py-1 rounded transition"
                                        title="{{ $question->is_active ? 'Deactivate' : 'Activate' }}"
                                    >
                                        {{ $question->is_active ? '👁️' : '👁️‍🗨️' }}
                                    </button>
                                    <button
                                        wire:click="confirmReset({{ $question->id }})"
                                        class="text-orange-500 hover:text-orange-700 px-2 py-1 rounded transition"
                                        title="Reset votes"
                                    >
                                        🔄
                                    </button>
                                    <button
                                        wire:click="confirmDelete({{ $question->id }})"
                                        class="text-red-500 hover:text-red-700 px-2 py-1 rounded transition"
                                        title="Delete"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>

                            <!-- Answer Options Display -->
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                Options: {{ implode(', ', $question->answer_choices) }}
                            </div>

                            <!-- Host Voting -->
                            @php
                                $hostVoted = $hostParticipant && $question->hasVoteFrom($hostParticipant);
                            @endphp
                            @if(!$hostVoted)
                                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <p class="text-sm text-blue-700 dark:text-blue-300 mb-2">Cast your vote:</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($question->answer_choices as $option)
                                            <button
                                                wire:click="vote({{ $question->id }}, '{{ addslashes($option) }}')"
                                                class="px-4 py-2 text-sm rounded-lg border-2 border-blue-300 dark:border-blue-700 hover:border-blue-500 hover:bg-blue-500 hover:text-white text-blue-700 dark:text-blue-300 font-medium transition"
                                            >
                                                {{ $option }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="mt-2 text-sm text-green-600 dark:text-green-400">✓ You voted: {{ $question->getVoteFrom($hostParticipant)?->vote_value }}</div>
                            @endif

                            <!-- Vote Distribution -->
                            @if($question->votes->count() > 0)
                                @php
                                    $votersByOption = $question->voters_by_option;
                                @endphp
                                <div class="mt-4">
                                    @if($question->hasNumericOptions() && $question->vote_average !== null)
                                        <div class="mb-3 p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                                            <span class="text-sm font-medium text-indigo-700 dark:text-indigo-300">📊 Average: {{ $question->vote_average }}</span>
                                        </div>
                                    @endif
                                    <div class="flex flex-wrap items-end gap-2">
                                        @foreach($question->answer_choices as $option)
                                            @php
                                                $count = $question->votes->where('vote_value', $option)->count();
                                                $percentage = $question->votes->count() > 0 ? ($count / $question->votes->count()) * 100 : 0;
                                                $voters = $votersByOption[$option] ?? [];
                                            @endphp
                                            <div class="flex-1 min-w-16 group relative">
                                                <div class="text-center text-xs text-gray-500 dark:text-gray-400 mb-1 truncate" title="{{ $option }}">{{ Str::limit($option, 10) }}</div>
                                                <div class="h-12 bg-gray-100 dark:bg-gray-700 rounded relative overflow-hidden cursor-pointer">
                                                    <div
                                                        class="absolute bottom-0 left-0 right-0 bg-primary transition-all duration-300 group-hover:bg-primary-dark"
                                                        style="height: {{ $percentage }}%"
                                                    ></div>
                                                </div>
                                                <div class="text-center text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $count }}</div>

                                                <!-- Hover tooltip showing voters -->
                                                @if(count($voters) > 0)
                                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block z-10">
                                                        <div class="bg-gray-900 text-white text-xs rounded-lg py-2 px-3 shadow-lg whitespace-nowrap max-w-48">
                                                            <div class="font-medium mb-1 border-b border-gray-700 pb-1">Voted "{{ $option }}":</div>
                                                            <div class="max-h-32 overflow-y-auto">
                                                                @foreach($voters as $voter)
                                                                    <div class="py-0.5">{{ $voter }}</div>
                                                                @endforeach
                                                            </div>
                                                            <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">No questions yet. Add your first question above!</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Participants Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-6 h-fit">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Participants</h2>
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @forelse($participants as $participant)
                    <div class="flex items-center justify-between gap-3 p-2 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center text-sm font-medium">
                                {{ strtoupper(substr($participant->username, 0, 1)) }}
                            </div>
                            <span class="text-gray-700 dark:text-gray-300">{{ $participant->username }}</span>
                        </div>
                        @unless($hostParticipant->id === $participant->id)
                            <button
                                wire:click="confirmKickParticipant({{ $participant->id }})"
                                class="text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 px-2 py-1 rounded transition"
                                title="Kick participant"
                            >
                                🚫
                            </button>
                        @endunless
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Waiting for participants...</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    @if($confirmingAction)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm" wire:click.self="cancelConfirmation">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 rounded-full {{ in_array($confirmingAction, ['delete', 'kick', 'endSession']) ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : ($confirmingAction === 'reset' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400') }}">
                            @if($confirmingAction === 'delete')
                                🗑️
                            @elseif($confirmingAction === 'reset')
                                🔄
                            @elseif($confirmingAction === 'kick')
                                🚫
                            @elseif($confirmingAction === 'endSession')
                                🛑
                            @else
                                👁️
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            @if($confirmingAction === 'toggle')
                                {{ $questions->find($confirmingId)?->is_active ? 'Deactivate' : 'Activate' }} Question
                            @elseif($confirmingAction === 'reset')
                                Reset Question
                            @elseif($confirmingAction === 'delete')
                                Delete Question
                            @elseif($confirmingAction === 'kick')
                                Kick Participant
                            @elseif($confirmingAction === 'endSession')
                                End Session
                            @endif
                        </h3>
                    </div>

                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        @if($confirmingAction === 'toggle')
                            Are you sure you want to {{ $questions->find($confirmingId)?->is_active ? 'deactivate' : 'activate' }} this question?
                            @if($questions->find($confirmingId)?->is_active)
                                Participants will no longer be able to see or vote on it.
                            @else
                                Participants will be able to see and vote on it again.
                            @endif
                        @elseif($confirmingAction === 'reset')
                            Are you sure you want to reset all votes for this question? This action cannot be undone.
                        @elseif($confirmingAction === 'delete')
                            Are you sure you want to delete this question? This action cannot be undone.
                        @elseif($confirmingAction === 'kick')
                            Are you sure you want to kick {{ $participants->find($confirmingId)?->username }}? This will remove them and their votes.
                        @elseif($confirmingAction === 'endSession')
                            Are you sure you want to end this session? This will redirect you to the home page.
                        @endif
                    </p>

                    <div class="flex justify-end gap-3">
                        <button
                            wire:click="cancelConfirmation"
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition font-medium"
                        >
                            Cancel
                        </button>
                        @if(in_array($confirmingAction, ['toggle', 'reset', 'delete']))
                            <button
                                wire:click="{{ $confirmingAction }}Question({{ $confirmingId }})"
                                class="px-4 py-2 {{ $confirmingAction === 'delete' ? 'bg-red-500 hover:bg-red-600' : ($confirmingAction === 'reset' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-primary hover:bg-primary-dark') }} text-white rounded-lg transition font-medium"
                            >
                                Confirm
                            </button>
                        @elseif($confirmingAction === 'kick')
                            <button
                                wire:click="kickParticipant({{ $confirmingId }})"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition font-medium"
                            >
                                Confirm Kick
                            </button>
                        @elseif($confirmingAction === 'endSession')
                            <button
                                wire:click="endSession"
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg transition font-medium"
                            >
                                Confirm End Session
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
