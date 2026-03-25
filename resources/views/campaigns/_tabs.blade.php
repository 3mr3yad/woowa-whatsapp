<div class="mb-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Campaigns</div>

        <nav class="flex flex-wrap items-center gap-2" aria-label="Tabs">
            <a href="{{ route('campaigns.index') }}"
               class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition-all duration-200 {{ request()->routeIs('campaigns.index') ? 'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-900/40 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-900/60' }}">
                Campaigns List
            </a>

            <a href="{{ route('campaigns.create') }}"
               class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition-all duration-200 {{ request()->routeIs('campaigns.create') ? 'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-900/40 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-900/60' }}">
                Create Campaign
            </a>

            @isset($campaign)
                <a href="{{ route('campaigns.show', $campaign) }}"
                   class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium transition-all duration-200 {{ request()->routeIs('campaigns.show') ? 'bg-gray-900 text-white shadow-sm dark:bg-white dark:text-gray-900' : 'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 hover:text-gray-900 dark:bg-gray-900/40 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-900/60' }}">
                    Campaign Details
                </a>
            @endisset
        </nav>
    </div>
</div>
