<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
            @if (Auth::user()->company)
                <span class="text-sm font-normal text-gray-500">— {{ Auth::user()->company->name }}</span>
            @endif
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($roleView === 'employee')
                @include('dashboard.partials.employee')
            @elseif ($roleView === 'admin')
                @include('dashboard.partials.admin')
            @else
                @include('dashboard.partials.superadmin')
            @endif
        </div>
    </div>
</x-app-layout>
