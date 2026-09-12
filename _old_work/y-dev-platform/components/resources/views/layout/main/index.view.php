@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div {{ $attributes->class('w-main-ctn') }}>
  {!! $slot !!}
</div>
