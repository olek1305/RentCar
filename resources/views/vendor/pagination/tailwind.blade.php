@if ($paginator->hasPages())
    <div class="flex justify-center mt-12">
        <nav class="flex items-center space-x-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-4 py-2 rounded-full text-slate-400 bg-slate-100 cursor-not-allowed">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
      </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="p-2 rounded-full text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors duration-200 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @endif

            {{-- Pagination Elements --}}
            <div class="flex items-center space-x-1 bg-slate-100 rounded-full p-1">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-3 py-1 text-slate-400">...</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-4 py-1 rounded-full bg-white text-slate-800 shadow-sm font-medium">
                {{ $page }}
              </span>
                            @else
                                <a href="{{ $url }}" class="px-4 py-1 rounded-full text-slate-600 hover:bg-white hover:text-slate-800 transition-colors duration-200">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="p-2 rounded-full text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors duration-200 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            @else
                <span class="px-4 py-2 rounded-full text-slate-400 bg-slate-100 cursor-not-allowed">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
        </svg>
      </span>
            @endif
        </nav>
    </div>
@endif
