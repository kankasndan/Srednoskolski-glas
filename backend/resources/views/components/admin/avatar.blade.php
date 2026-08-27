@props(['src' => null, 'user' => null, 'size' => 'md'])

@php
    $url = $src ?? $user?->imageUrl;
    $sizeClass = match ($size) {
        'sm' => 'h-8 w-8 text-xs',
        'lg' => 'h-14 w-14 text-lg',
        'xl' => 'h-20 w-20 text-xl',
        '2xl' => 'h-24 w-24 text-2xl',
        default => 'h-10 w-10 text-sm',
    };
@endphp

@if ($url)
    <img src="{{ $url }}" alt=""
        {{ $attributes->merge(['class' => $sizeClass.' shrink-0 rounded-full object-cover bg-gray-100']) }}>
@else
    <span
        {{ $attributes->merge(['class' => 'flex '.$sizeClass.' shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400']) }}>
        <i class="fa-regular fa-user"></i>
    </span>
@endif
