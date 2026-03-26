<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @include('campaigns._tabs')

            <div class="bg-white/80 dark:bg-gray-900/40 backdrop-blur rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">System Logs</h1>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $path }}</div>
                    </div>

                    <form method="GET" class="flex items-center gap-2">
                        <label class="text-sm text-gray-600 dark:text-gray-300">Lines</label>
                        <input type="number" name="lines" value="{{ $lines }}" min="50" max="2000"
                               class="w-28 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm outline-none transition focus:border-gray-900 focus:ring-2 focus:ring-gray-900/10 dark:border-gray-700 dark:bg-gray-900/40 dark:text-gray-100 dark:focus:border-white dark:focus:ring-white/10">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                            Refresh
                        </button>
                    </form>
                </div>

                <div class="mt-6 rounded-2xl ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden bg-gray-50/70 dark:bg-gray-900/60">
                    <pre class="p-4 text-xs leading-relaxed overflow-auto max-h-[70vh] whitespace-pre-wrap break-words text-gray-800 dark:text-gray-200">{{ $content }}</pre>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
