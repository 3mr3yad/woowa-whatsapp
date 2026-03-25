<x-app-layout>
    <div class="py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('campaigns._tabs')

            <div class="mb-6">
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Create Campaign</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Upload contacts and prepare your message before sending.</p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                    <div class="font-semibold">Please fix the following:</div>
                    <ul class="mt-2 list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">{{ session('error') }}</div>
            @endif

        <form x-data="{ fileName: '', uploading: false }"
              @submit="uploading = true"
              action="{{ route('campaigns.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5 bg-white/80 dark:bg-gray-900/40 backdrop-blur p-6 sm:p-8 rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800">
            @csrf

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">Title</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:focus:border-white dark:focus:ring-white/10">
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">Message</label>
                <textarea name="message" rows="8"
                          class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:focus:border-white dark:focus:ring-white/10">{{ old('message') }}</textarea>
            </div>

            <div>
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-gray-100">Excel File</label>
                <input type="file" name="file"
                       @change="fileName = $event.target.files && $event.target.files.length ? $event.target.files[0].name : ''"
                       class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 shadow-sm outline-none transition file:mr-4 file:rounded-lg file:border-0 file:bg-gray-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-gray-800 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:file:bg-white dark:file:text-gray-900 dark:hover:file:bg-gray-100">

                <div x-show="fileName" class="mt-2 text-xs text-gray-600 dark:text-gray-300" style="display: none;">
                    Selected: <span class="font-medium" x-text="fileName"></span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('campaigns.index') }}"
                   class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 ring-gray-200 dark:ring-gray-700 transition-all duration-200 hover:bg-gray-50 hover:-translate-y-0.5 active:translate-y-0 dark:hover:bg-gray-900/60">
                    Back
                </a>
                <button type="submit"
                        :disabled="!fileName || uploading"
                        class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-gray-800 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:-translate-y-0 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                    <span x-show="!uploading">Save Campaign</span>
                    <span x-show="uploading" style="display: none;">Uploading...</span>
                </button>
            </div>
        </form>
        </div>
    </div>
</x-app-layout>