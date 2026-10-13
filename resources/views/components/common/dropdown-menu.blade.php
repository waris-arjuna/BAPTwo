@props(['items' => ['View More','Delete']])
<div x-data="{openDropDown: false}" class="relative h-fit">
    <button
        @click="openDropDown = !openDropDown"
        :class="openDropDown ? 'text-gray-700 dark:text-white' : 'text-gray-400 hover:text-gray-700 dark:hover:text-white'"
    >
        <i class="fi fi-rr-menu-dots-vertical text-base" aria-hidden="true"></i>
    </button>
    
    <div x-show="openDropDown" @click.outside="openDropDown = false" 
         class="absolute ltr:right-0 rtl:left-0 ltr:left-auto rtl:right-auto z-40 w-40 p-2 space-y-1 bg-white border border-gray-200 shadow-theme-lg dark:bg-gray-dark top-full rounded-2xl dark:border-gray-800"
         x-cloak>
        @forelse($items as $item)
            <button class="flex w-full px-3 py-2 font-medium ltr:text-left rtl:text-right text-gray-500 rounded-lg text-theme-xs hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300">
                {{ __($item) }}
            </button>
        @empty
            {{ $slot }}
        @endforelse
    </div>
</div>
