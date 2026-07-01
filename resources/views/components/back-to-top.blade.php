<button
    data-back-to-top
    aria-label="{{ __('Kembali ke atas') }}"
    class="fixed bottom-6 right-6 z-50 invisible flex items-center gap-3 bg-transparent border-0 p-0 cursor-pointer outline-none"
>
    <span
        data-back-to-top-tooltip
        class="absolute right-full mr-3 translate-x-2 opacity-0 pointer-events-none whitespace-nowrap bg-primary text-white text-xs font-medium px-3 py-1.5 rounded-lg shadow-lg"
    >
        {{ __('Kembali ke atas') }}
    </span>

    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36" data-back-to-top-ring>
        <circle
            cx="18" cy="18" r="16"
            fill="white"
            stroke="oklch(0.87 0.01 286)"
            stroke-width="2"
        />
        <circle
            cx="18" cy="18" r="16"
            fill="none"
            stroke="oklch(0.48 0.29 268)"
            stroke-width="2"
            stroke-linecap="round"
        />
    </svg>

    <span class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
        </svg>
    </span>
</button>
