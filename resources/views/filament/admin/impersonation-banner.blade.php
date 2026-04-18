@if(app('impersonate')->isImpersonating())
<div class="bg-amber-500 text-white text-sm font-medium px-4 py-2 flex items-center justify-between">
    <span>
        You are impersonating <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
    </span>
    <a href="{{ route('impersonate.leave') }}"
       class="bg-white text-amber-700 font-semibold px-3 py-1 rounded text-xs hover:bg-amber-50 transition-colors">
        Leave &amp; Return to Admin
    </a>
</div>
@endif
