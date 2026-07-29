@extends('layouts.admin')

@section('content')
    <section class="mt-10">
        <h1 class="text-3xl font-semibold sm:text-4xl">Utilities and Maintenance</h1>
        <p class="mt-2 text-sm sm:text-base text-muted">Various tools and checks to make sure everything is up and running properly!</p>
    </section>
    <x-panel class="mt-8 grid gap-4 p-6">
        {{-- kinda janky but 27px is ~2.5xl --}}
        <h1 class="font-semibold text-2xl sm:text-[27px]">DNS Desynchronization Check</h1>
        <form method="POST" action="{{ route('admin.action') }}">
            @csrf
            <input type="hidden" name="name" value="desync">
            <input type="hidden" name="user_id" value="desync">
            <x-button variant="accent">
                <x-materialsymbols icon="motion-play" />
                Run Check
            </x-button>
        </form>
        @if (session('desync'))
            @php
                $desync = session('desync');
            @endphp
            <div class="grid gap-3 border-t border-border pt-4">
                <div class="flex flex-col">
                    <h2 class="text-xl font-semibold">Report</h2>
                    <span class="text-sm font-light">Record Counts: MongoDB: {{ $desync['mongo_keys'] ?? 0 }}, PowerDNS: {{ $desync['powerdns_keys'] ?? 0 }}</span>
                </div>
                @foreach (($desync['issues'] ?? []) as $title => $entries)
                    @if ($entries)
                        @php
                            $friendlyMap = [
                                "duplicate_mongo" => "Duplicate records in MongoDB",
                                "missing_from_powerdns" => "Missing from PowerDNS",
                                "missing_from_mongo" => "Missing from MongoDB",
                                "value_mismatches" => "Value mismatches",
                            ];
                        @endphp
                        <div>
                            <h3 class="text-lg font-medium">
                                {{ $friendlyMap[$title] ?? str_replace('_', ' ', $title) }}
                            </h3>
                            <ul class="mt-1 list-disc pl-6 text-sm">
                                @foreach ($entries as $entry)
                                    <li><code>{{ $entry }}</code></li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
                @if (!collect($desync['issues'] ?? [])->flatten()->count())<p class="text-sm text-success">No desynchronization issues found.</p>@endif
            </div>
        @endif
    </x-panel>
@endsection
