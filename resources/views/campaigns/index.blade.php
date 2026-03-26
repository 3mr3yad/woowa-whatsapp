<x-app-layout>
    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @include('campaigns._tabs')

            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Campaigns</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your campaigns and track delivery status.</p>
                </div>

                <a href="{{ route('campaigns.create') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-gray-800 hover:-translate-y-0.5 active:translate-y-0 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100">
                    New Campaign Ayad
                </a>
            </div>

            <div class="bg-white/80 dark:bg-gray-900/40 backdrop-blur rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50/70 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left font-semibold">Title</th>
                                <th class="px-6 py-4 text-left font-semibold">Status</th>
                                <th class="px-6 py-4 text-left font-semibold">Created</th>
                                <th class="px-6 py-4 text-right font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($campaigns as $campaign)
                                <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-900/50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $campaign->title }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset
                                            {{ $campaign->status === 'completed' ? 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-900/30 dark:text-green-200 dark:ring-green-800' :
                                               ($campaign->status === 'processing' ? 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-200 dark:ring-blue-800' :
                                               ($campaign->status === 'failed' ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/30 dark:text-red-200 dark:ring-red-800' :
                                               'bg-gray-50 text-gray-700 ring-gray-200 dark:bg-gray-800/50 dark:text-gray-200 dark:ring-gray-700')) }}">
                                            {{ $campaign->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $campaign->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('campaigns.show', $campaign) }}"
                                           class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 ring-gray-200 dark:ring-gray-700 transition-all duration-200 hover:bg-gray-50 hover:-translate-y-0.5 active:translate-y-0 dark:hover:bg-gray-900/60">
                                            View
                                        </a>

                                        <a href="{{ route('campaigns.edit', $campaign) }}"
                                           class="ml-2 inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 ring-1 ring-gray-200 dark:ring-gray-700 transition-all duration-200 hover:bg-gray-50 hover:-translate-y-0.5 active:translate-y-0 dark:hover:bg-gray-900/60">
                                            Edit
                                        </a>

                                        <form action="{{ route('campaigns.destroy', $campaign) }}" method="POST" class="ml-2 inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Delete this campaign? This will remove its contacts and logs.')"
                                                    class="inline-flex items-center rounded-xl px-3 py-2 text-sm font-semibold text-red-700 ring-1 ring-red-200 transition-all duration-200 hover:bg-red-50 hover:-translate-y-0.5 active:translate-y-0 dark:text-red-200 dark:ring-red-800 dark:hover:bg-red-900/20">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
</x-app-layout>