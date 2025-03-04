<x-filament-panels::page>
    <div class="space-y-2">
        <div class="relative overflow-x-auto rounded-xl border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-800">
            <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-gray-600 dark:text-gray-300">Name</th>
                        <th scope="col" class="px-6 py-3 text-gray-600 dark:text-gray-300">Ranges</th>
                        <th scope="col" class="px-6 py-3 text-right text-gray-600 dark:text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pools as $pool)
                        <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $pool['name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $pool['ranges'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex justify-end space-x-2">
                                    <button 
                                        wire:click="deletePool('{{ $pool['name'] }}')"
                                        wire:confirm="Are you sure you want to delete this IP pool?"
                                        type="button"
                                        class="inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset min-h-[2rem] px-3 text-sm text-red shadow focus:ring-white border-transparent bg-red-600 hover:bg-red-500 focus:bg-red-700 focus:ring-offset-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                No IP pools found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
