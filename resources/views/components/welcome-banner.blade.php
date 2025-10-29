{{-- Welcome Banner Component --}}
@props(['title', 'subtitle', 'icon' => 'fas fa-industry'])

<div class="bg-green-800 rounded-xl sm:rounded-2xl p-2 lg:px-8 sm:p-4 lg:p-4 mb-4 sm:mb-6 lg:mb-8 text-white shadow-lg mt-2 sm:mt-4 lg:mt-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg sm:text-xl lg:text-xl font-bold mb-1 sm:mb-2">{{ $title }}</h1>
            <p class="text-white text-xs sm:text-base lg:text-md">{{ $subtitle }}</p>
        </div>
        <div class="hidden lg:block">
            <i class="{{ $icon }} text-4xl text-white"></i>
        </div>
    </div>
</div>
