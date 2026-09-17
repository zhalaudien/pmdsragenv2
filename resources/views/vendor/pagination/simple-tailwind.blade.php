@if ($paginator->hasPages() || $paginator->count() > 0)
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs w-full">
        <div class="text-slate-500 order-2 sm:order-1 text-center sm:text-left">
            @if ($paginator->firstItem())
                <span>Menampilkan <span class="font-bold text-slate-800">{{ number_format($paginator->firstItem()) }}</span> - <span class="font-bold text-slate-800">{{ number_format($paginator->lastItem()) }}</span> dari <span class="font-bold text-slate-800">{{ number_format($paginator->total()) }}</span> data</span>
            @else
                <span>Menampilkan <span class="font-bold text-slate-800">{{ number_format($paginator->count()) }}</span> data</span>
            @endif
        </div>

        @if ($paginator->hasPages())
            <div class="flex items-center gap-1.5 order-1 sm:order-2">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman Sebelumnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100/60 text-slate-400 cursor-not-allowed select-none text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman Sebelumnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-red-600 hover:border-slate-300 font-semibold text-xs transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span>Sebelumnya</span>
                    </a>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman Selanjutnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-red-600 hover:border-slate-300 font-semibold text-xs transition shadow-xs">
                        <span>Selanjutnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman Selanjutnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100/60 text-slate-400 cursor-not-allowed select-none text-xs font-semibold">
                        <span>Selanjutnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </div>
        @endif
    </nav>
@endif
