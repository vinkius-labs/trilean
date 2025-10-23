@php
$stateEnum = ternary($state ?? \VinkiusLabs\Trilean\Enums\TernaryState::UNKNOWN);
$attributeBag = ($attributes ?? new \Illuminate\View\ComponentAttributeBag())->class([
'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
config('trilean.ui.badges.' . $stateEnum->value, 'bg-slate-200 text-slate-800'),
]);

$icon = config('trilean.ui.icons.' . $stateEnum->value);

$slotContent = null;
if (isset($slot) && $slot instanceof \Illuminate\View\ComponentSlot && ! $slot->isEmpty()) {
$slotContent = trim((string) $slot);
}

$label = $slotContent !== null && $slotContent !== ''
? $slotContent
: ($label ?? ternary_match($stateEnum, [
'true' => __('TRUE'),
'false' => __('FALSE'),
'unknown' => __('UNKNOWN'),
'any' => fn ($value) => ucfirst((string) $value),
]));
@endphp

<span {{ $attributeBag }}>
    @if ($icon)
    <i class="-ml-1 mr-1 h-3 w-3 {{ e($icon) }}" aria-hidden="true"></i>
    @endif
    {{ $label }}
</span>