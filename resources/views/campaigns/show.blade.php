<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @include('campaigns._tabs', ['campaign' => $campaign])

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-200">{{ session('success') }}</div>
            @endif

            <div class="bg-white/80 dark:bg-gray-900/40 backdrop-blur rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $campaign->title }}</h1>
                        <p class="mt-3 whitespace-pre-line text-gray-700 dark:text-gray-200">{{ $campaign->message }}</p>

                        <div class="mt-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Status</span>
                            <div class="mt-1">
                                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset
                                    {{ $campaign->status === 'completed' ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' :
                                       ($campaign->status === 'processing' ? 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:ring-blue-800' :
                                       ($campaign->status === 'failed' ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' :
                                       'bg-gray-50 text-gray-700 ring-gray-200 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700')) }}">
                                    {{ $campaign->status }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="sm:mt-1 w-full sm:w-auto">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
                            <button id="sendBtn"
                                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-emerald-500 hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-60 disabled:cursor-not-allowed disabled:hover:-translate-y-0">
                                <span id="sendBtnText">Send Now</span>
                            </button>

                            <a href="{{ route('campaigns.edit', $campaign) }}"
                               class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 ring-gray-200 dark:ring-gray-700 transition-all duration-200 hover:bg-gray-50 hover:-translate-y-0.5 active:translate-y-0 dark:hover:bg-gray-900/60">
                                Edit
                            </a>

                            <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Delete this campaign? This will remove its contacts and logs.')"
                                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-red-700 ring-1 ring-red-200 transition-all duration-200 hover:bg-red-50 hover:-translate-y-0.5 active:translate-y-0 dark:text-red-200 dark:ring-red-800 dark:hover:bg-red-900/20">
                                    Delete
                                </button>
                            </form>
                        </div>

                        <div id="progressWrap" class="mt-4" style="display: none;">
                            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
                                <div id="progressText">0/0</div>
                                <div id="progressPercent">0%</div>
                            </div>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                                <div id="progressBar" class="h-2 rounded-full bg-emerald-600 transition-all duration-300" style="width: 0%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                (function () {
                    const sendBtn = document.getElementById('sendBtn');
                    const sendBtnText = document.getElementById('sendBtnText');
                    const progressWrap = document.getElementById('progressWrap');
                    const progressText = document.getElementById('progressText');
                    const progressPercent = document.getElementById('progressPercent');
                    const progressBar = document.getElementById('progressBar');

                    const sendUrl = @json(route('campaigns.send', $campaign));
                    const progressUrl = @json(route('campaigns.progress', $campaign));
                    const csrf = @json(csrf_token());

                    let pollTimer = null;

                    async function fetchProgress() {
                        const res = await fetch(progressUrl, {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (!res.ok) {
                            throw new Error('Progress request failed');
                        }
                        return await res.json();
                    }

                    function setProgress(done, total, percent) {
                        progressText.textContent = `${done}/${total}`;
                        progressPercent.textContent = `${percent}%`;
                        progressBar.style.width = `${percent}%`;
                    }

                    async function startPolling() {
                        progressWrap.style.display = '';

                        const tick = async () => {
                            try {
                                const p = await fetchProgress();
                                setProgress(p.done ?? 0, p.total ?? 0, p.percent ?? 0);

                                if (p.status === 'completed' || p.status === 'failed') {
                                    clearInterval(pollTimer);
                                    pollTimer = null;
                                    sendBtn.disabled = false;
                                    sendBtnText.textContent = 'Send Now';
                                    setTimeout(() => window.location.reload(), 800);
                                }
                            } catch (e) {
                                clearInterval(pollTimer);
                                pollTimer = null;
                                sendBtn.disabled = false;
                                sendBtnText.textContent = 'Send Now';
                            }
                        };

                        await tick();
                        pollTimer = setInterval(tick, 1500);
                    }

                    sendBtn?.addEventListener('click', async function () {
                        if (sendBtn.disabled) return;

                        sendBtn.disabled = true;
                        sendBtnText.textContent = 'Starting...';

                        try {
                            await fetch(sendUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': csrf,
                                    'Accept': 'application/json'
                                }
                            });

                            sendBtnText.textContent = 'Sending...';
                            await startPolling();
                        } catch (e) {
                            sendBtn.disabled = false;
                            sendBtnText.textContent = 'Send Now';
                        }
                    });
                })();
            </script>

            <div class="bg-white/80 dark:bg-gray-900/40 backdrop-blur rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">
                <div class="px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Contacts</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/70 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Name</th>
                                <th class="px-6 py-4 text-left font-semibold">Phone</th>
                                <th class="px-6 py-4 text-left font-semibold">Done</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($campaign->contacts as $contact)
                                <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $contact->name }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $contact->phone }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $contact->done_send ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' : 'bg-gray-50 text-gray-700 ring-gray-200 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700' }}">
                                            {{ $contact->done_send ? 'Yes' : 'No' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-900/40 backdrop-blur rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">
                <div class="px-6 py-5">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Logs</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/70 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Name</th>
                                <th class="px-6 py-4 text-left font-semibold">Phone</th>
                                <th class="px-6 py-4 text-left font-semibold">Message</th>
                                <th class="px-6 py-4 text-left font-semibold">Status</th>
                                <th class="px-6 py-4 text-left font-semibold">HTTP</th>
                                <th class="px-6 py-4 text-left font-semibold">Sent At</th>
                                <th class="px-6 py-4 text-left font-semibold">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($campaign->logs as $log)
                                <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $log->name }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $log->phone }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                        <div class="max-w-xl whitespace-pre-line break-words">{{ $log->message }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $log->status === 'success' ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' : 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $log->http_code }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ optional($log->sent_at)->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                        @if($log->status !== 'success')
                                            <details class="group">
                                                <summary class="cursor-pointer select-none text-sm font-semibold text-gray-900 dark:text-gray-100">View</summary>
                                                <div class="mt-2 space-y-2">
                                                    @if($log->error_message)
                                                        <div>
                                                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">Error</div>
                                                            <div class="whitespace-pre-line break-words text-xs">{{ $log->error_message }}</div>
                                                        </div>
                                                    @endif
                                                    @if($log->api_response)
                                                        <div>
                                                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">API Response</div>
                                                            <div class="whitespace-pre-line break-words text-xs">{{ $log->api_response }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </details>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>