@props(['title', 'description' => null, 'icon' => 'fa-inbox'])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white px-6 py-16 text-center shadow-sm']) }}>
    <div
        class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-my-purple/10 text-my-purple">
        <i class="fa-regular {{ $icon }} text-lg"></i>
    </div>
    <p class="text-sm font-medium text-gray-800">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-gray-400">{{ $description }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
