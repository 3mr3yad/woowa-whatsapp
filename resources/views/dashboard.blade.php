<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">Overview of campaigns, delivery and system readiness.</div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur p-5 ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Messages Sent (Success)</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $logCounts['success'] ?? 0 }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Out of {{ $logCounts['total'] ?? 0 }} attempts</div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur p-5 ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Messages Failed</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $logCounts['failed'] ?? 0 }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Check logs for error details</div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur p-5 ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Contacts</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $contactCounts['total'] ?? 0 }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Sent: {{ $contactCounts['sent'] ?? 0 }} · Pending: {{ $contactCounts['pending'] ?? 0 }}</div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur p-5 ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm">
                    <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Campaigns</div>
                    <div class="mt-2 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $campaignCounts['total'] ?? 0 }}</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Draft: {{ $campaignCounts['draft'] ?? 0 }} · Processing: {{ $campaignCounts['processing'] ?? 0 }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm overflow-hidden lg:col-span-2">
                    <div class="px-6 py-5 flex items-center justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent Campaigns</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Latest campaigns and delivery results.</div>
                        </div>
                        <a href="{{ route('campaigns.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-600">View all</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50/70 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold">Title</th>
                                    <th class="px-6 py-4 text-left font-semibold">Status</th>
                                    <th class="px-6 py-4 text-left font-semibold">Contacts</th>
                                    <th class="px-6 py-4 text-left font-semibold">Success</th>
                                    <th class="px-6 py-4 text-left font-semibold">Failed</th>
                                    <th class="px-6 py-4 text-right font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($recentCampaigns as $c)
                                    <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-900/50">
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $c->title }}</td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset
                                                {{ $c->status === 'completed' ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' :
                                                   ($c->status === 'processing' ? 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:ring-blue-800' :
                                                   ($c->status === 'failed' ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' :
                                                   'bg-gray-50 text-gray-700 ring-gray-200 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700')) }}">
                                                {{ $c->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $c->contacts_count }}</td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $c->success_logs_count }}</td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $c->failed_logs_count }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('campaigns.show', $c) }}" class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 ring-gray-200 dark:ring-gray-700 transition-all duration-200 hover:bg-gray-50 hover:-translate-y-0.5 active:translate-y-0 dark:hover:bg-gray-900/60">Open</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">No campaigns yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm overflow-hidden">
                    <div class="px-6 py-5">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">System Readiness</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Things that often cause failures.</div>
                    </div>

                    <div class="px-6 pb-6 space-y-3">
                        <div class="flex items-start justify-between gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notif API URL</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300 break-all">{{ $system['notifapi_url'] ?: 'Not set' }}</div>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $system['notifapi_url'] ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' : 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' }}">
                                {{ $system['notifapi_url'] ? 'OK' : 'Missing' }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notif API Key</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $system['notifapi_key_set'] ? 'Configured' : 'Not set' }}</div>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $system['notifapi_key_set'] ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' : 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' }}">
                                {{ $system['notifapi_key_set'] ? 'OK' : 'Missing' }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-3 rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Queue Connection</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-300">{{ $system['queue_connection'] ?: 'Not set' }}</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">For progress + delay sending, use database/redis and run a worker.</div>
                            </div>
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ ($system['queue_connection'] && $system['queue_connection'] !== 'sync') ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' : 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-900/20 dark:text-amber-200 dark:ring-amber-800' }}">
                                {{ ($system['queue_connection'] && $system['queue_connection'] !== 'sync') ? 'OK' : 'Check' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white/80 dark:bg-gray-900/40 backdrop-blur ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm p-6">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Campaign Status Breakdown</div>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Draft</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $campaignCounts['draft'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Processing</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $campaignCounts['processing'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Completed</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $campaignCounts['completed'] ?? 0 }}</div>
                    </div>
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/60 p-4 ring-1 ring-gray-200 dark:ring-gray-800">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400">Failed</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $campaignCounts['failed'] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
