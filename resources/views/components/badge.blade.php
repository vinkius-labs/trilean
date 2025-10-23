<span {{ $attributes->class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
    config('trilean.ui.badges.' . ternary($state)->value, 'bg-slate-200 text-slate-800'),
]) }}>
    @php($icon = config('trilean.ui.icons.' . ternary($state)->value))
    @if ($icon)
    <x-dynamic-component :component="$icon" class="-ml-1 mr-1 h-3 w-3" />
    @endif
    {{ $slot->isEmpty() ? ternary_match($state, [
        'true' => __('TRUE'),
        'false' => __('FALSE'),
        'unknown' => __('UNKNOWN'),
        'any' => fn ($value) => ucfirst($value),
    ]) : $slot }}
</span>