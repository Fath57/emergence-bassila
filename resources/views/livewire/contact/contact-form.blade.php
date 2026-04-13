<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-[#333333] mb-4">Envoyer un message</h2>

    @if ($sent)
        <div class="flex items-start gap-3 bg-green-50 border border-green-200 text-green-800 rounded-xl p-4">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="font-medium text-sm">Message envoyé !</p>
                <p class="text-sm mt-0.5">Votre message a été transmis. Une confirmation vous a été envoyée par email.</p>
            </div>
        </div>
    @else
        <form wire:submit.prevent="send" class="space-y-4">
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Sujet</label>
                <input
                    wire:model="subject"
                    id="subject"
                    type="text"
                    placeholder="Objet de votre message"
                    maxlength="255"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] @error('subject') border-red-400 @enderror"
                >
                @error('subject') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea
                    wire:model="message"
                    id="message"
                    rows="5"
                    placeholder="Écrivez votre message ici..."
                    maxlength="2000"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0066CC] resize-none @error('message') border-red-400 @enderror"
                ></textarea>
                <div class="flex justify-between mt-1">
                    @error('message')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @else
                        <span></span>
                    @enderror
                    <p class="text-xs text-gray-400">{{ mb_strlen($message ?? '') }}/2000</p>
                </div>
            </div>

            <button
                type="submit"
                class="w-full bg-[#0066CC] hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition text-sm"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-75 cursor-not-allowed"
            >
                <span wire:loading.remove>Envoyer le message</span>
                <span wire:loading>Envoi en cours...</span>
            </button>
        </form>
    @endif
</div>
