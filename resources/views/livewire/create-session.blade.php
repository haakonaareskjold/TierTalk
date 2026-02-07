<div class="px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Create a TierTalk Session</h1>

            <form wire:submit="createSession" class="space-y-6">
                <!-- Session Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Session Title (optional)
                    </label>
                    <input
                        type="text"
                        id="title"
                        wire:model="title"
                        placeholder="e.g., Team Retrospective"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Questions -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Questions & Answer Options
                    </label>
                    <div class="space-y-6">
                        @foreach($questions as $qIndex => $question)
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex gap-2 mb-3">
                                    <div class="flex-1">
                                        <input
                                            type="text"
                                            wire:model="questions.{{ $qIndex }}.text"
                                            placeholder="Enter your question..."
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                        >
                                        @error('questions.' . $qIndex . '.text') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    @if(count($questions) > 1)
                                        <button
                                            type="button"
                                            wire:click="removeQuestion({{ $qIndex }})"
                                            class="px-3 py-2 text-red-500 hover:bg-red-100 rounded-lg transition"
                                            title="Remove question"
                                        >
                                            ✕
                                        </button>
                                    @endif
                                </div>

                                <!-- Answer Options -->
                                <div class="ml-4">
                                    <label class="block text-xs font-medium text-gray-500 mb-2">Answer Options</label>
                                    <div class="space-y-2">
                                        @foreach($question['options'] as $oIndex => $option)
                                            <div class="flex gap-2 items-center">
                                                <span class="text-gray-400 text-sm w-6">{{ $oIndex + 1 }}.</span>
                                                <input
                                                    type="text"
                                                    wire:model="questions.{{ $qIndex }}.options.{{ $oIndex }}"
                                                    placeholder="e.g., Yes, No, Messi, Ronaldo..."
                                                    class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                                >
                                                @if(count($question['options']) > 2)
                                                    <button
                                                        type="button"
                                                        wire:click="removeOption({{ $qIndex }}, {{ $oIndex }})"
                                                        class="px-2 py-1 text-red-400 hover:text-red-600 hover:bg-red-50 rounded transition"
                                                        title="Remove option"
                                                    >
                                                        ✕
                                                    </button>
                                                @endif
                                            </div>
                                            @error('questions.' . $qIndex . '.options.' . $oIndex) <span class="text-red-500 text-xs ml-6">{{ $message }}</span> @enderror
                                        @endforeach
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="addOption({{ $qIndex }})"
                                        class="mt-2 text-xs text-primary hover:text-primary-dark font-medium"
                                    >
                                        + Add option
                                    </button>
                                    @error('questions.' . $qIndex . '.options') <span class="text-red-500 text-xs block mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button
                        type="button"
                        wire:click="addQuestion"
                        class="mt-3 text-primary hover:text-primary-dark font-medium text-sm"
                    >
                        + Add another question
                    </button>
                </div>

                <!-- Max Participants -->
                <div>
                    <label for="maxParticipants" class="block text-sm font-medium text-gray-700 mb-1">
                        Maximum Participants
                    </label>
                    <input
                        type="number"
                        id="maxParticipants"
                        wire:model="maxParticipants"
                        min="2"
                        max="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                    @error('maxParticipants') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Expiration -->
                <div>
                    <label for="expirationHours" class="block text-sm font-medium text-gray-700 mb-1">
                        Session Duration (hours)
                    </label>
                    <select
                        id="expirationHours"
                        wire:model="expirationHours"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                    >
                        <option value="1">1 hour</option>
                        <option value="2">2 hours</option>
                        <option value="4">4 hours</option>
                        <option value="8">8 hours</option>
                        <option value="12">12 hours</option>
                        <option value="24">24 hours</option>
                    </select>
                    @error('expirationHours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-6 rounded-lg transition"
                >
                    Create Session
                </button>
            </form>
        </div>
    </div>
</div>
