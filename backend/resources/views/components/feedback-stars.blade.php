@props(['rating', 'size' => 'sm'])
@php
    $rating = (int) $rating;
    $iconSize = $size === 'lg' ? 'text-xl' : ($size === 'md' ? 'text-sm' : 'text-[11px]');
@endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5 leading-none']) }} aria-label="{{ $rating }} од 5">
    @for ($i = 1; $i <= 5; $i++)
        <i
            class="fa-{{ $i <= $rating ? 'solid' : 'regular' }} fa-star {{ $iconSize }} {{ $i <= $rating ? 'text-my-purple' : 'text-gray-300' }}"></i>
    @endfor
</span>
