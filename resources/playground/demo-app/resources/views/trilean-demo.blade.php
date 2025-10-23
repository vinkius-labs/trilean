@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-12">
    <h1 class="text-2xl font-semibold mb-6">Trilean Demo</h1>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm p-6 space-y-4">
        <div>
            <h2 class="text-lg font-semibold">Resultado</h2>
            <p class="text-slate-600">Estado atual: <strong>{{ strtoupper($state->value) }}</strong></p>
            <p class="text-slate-600">Vector codificado: <code>{{ $encoded }}</code></p>
        </div>

        <div>
            <h2 class="text-lg font-semibold">Decisões</h2>
            <ul class="space-y-2">
                @foreach ($report->decisions() as $decision)
                <li class="border border-slate-100 rounded-md p-3">
                    <div class="flex items-center justify-between">
                        <span class="font-medium">{{ $decision->name }}</span>
                        <span class="text-xs uppercase tracking-wide text-slate-500">{{ $decision->operator }}</span>
                    </div>
                    <p class="text-sm text-slate-600">Estado: {{ strtoupper($decision->state->value) }}</p>
                    @if ($decision->description)
                    <p class="text-sm text-slate-500 mt-1">{{ $decision->description }}</p>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection