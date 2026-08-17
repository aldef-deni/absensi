@props(['companies' => collect(), 'companyId' => null])

@if (auth()->user()->isSuperAdmin() && $companies->count() > 1)
    <form method="GET" action="{{ url()->current() }}" class="inline-flex items-center gap-2" title="Pilih perusahaan yang dikelola">
        @foreach (request()->except(['company_id']) as $key => $value)
            @if (is_array($value))
                @foreach ($value as $item)
                    <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>

        <select name="company_id" onchange="this.form.submit()"
            class="rounded-lg border-gray-200 bg-gray-50 text-sm font-medium text-gray-700 focus:border-primary-500 focus:ring-primary-500/20 shadow-sm py-1.5 pl-2 pr-8">
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected($company->id === (int) $companyId)>{{ $company->name }}</option>
            @endforeach
        </select>
    </form>
@endif
