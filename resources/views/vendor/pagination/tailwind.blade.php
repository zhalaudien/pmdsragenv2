@if ($paginator->hasPages() || $paginator->total() > 0)
    <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs w-full">
        
        {{-- Information text on the left --}}
        <div class="text-slate-500 flex items-center gap-1.5 order-2 sm:order-1 text-center sm:text-left">
            @if ($paginator->firstItem())
                <span>Menampilkan <span class="font-bold text-slate-800">{{ number_format($paginator->firstItem()) }}</span> - <span class="font-bold text-slate-800">{{ number_format($paginator->lastItem()) }}</span> dari <span class="font-bold text-slate-800">{{ number_format($paginator->total()) }}</span> data</span>
            @else
                <span>Menampilkan <span class="font-bold text-slate-800">{{ number_format($paginator->count()) }}</span> data</span>
            @endif
        </div>

        {{-- Navigation controls on the right --}}
        @if ($paginator->hasPages())
            <div class="flex items-center gap-1 sm:gap-1.5 order-1 sm:order-2">

                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="Halaman Sebelumnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100/70 text-slate-400 cursor-not-allowed select-none text-xs font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman Sebelumnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-red-600 hover:border-slate-300 font-semibold text-xs transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </a>
                @endif

                {{-- Numeric Page Links (Desktop) --}}
                <div class="hidden sm:flex items-center gap-1">
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true" class="inline-flex items-center justify-center min-w-[34px] h-[34px] px-1 text-slate-400 font-bold select-none text-xs tracking-wider">
                                &hellip;
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="inline-flex items-center justify-center min-w-[34px] h-[34px] px-2.5 rounded-xl bg-red-600 text-white font-bold text-xs shadow-xs border border-red-600 select-none">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" aria-label="Halaman {{ $page }}" class="inline-flex items-center justify-center min-w-[34px] h-[34px] px-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-red-600 hover:border-slate-300 font-semibold text-xs transition shadow-xs">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Mobile Page Indicator --}}
                <div class="sm:hidden px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-bold text-xs shadow-xs">
                    <span>{{ $paginator->currentPage() }}</span> / <span>{{ $paginator->lastPage() }}</span>
                </div>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman Selanjutnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-red-600 hover:border-slate-300 font-semibold text-xs transition shadow-xs">
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span aria-disabled="true" aria-label="Halaman Selanjutnya" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100/60 text-slate-400 cursor-not-allowed select-none text-xs font-semibold">
                        <span class="hidden sm:inline">Selanjutnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif

            </div>
        @endif

    </nav>
@endif
