{{--
  <x-webkernel::page title="..." description="..." :csrf="false">
    @slot('actions') ... @endslot
    body
  </x-webkernel::page>
--}}
@props([
  'title' => null,
  'description' => null,
  'csrf' => true,
])
@if ($csrf)
  {!! \Webkernel\Csrf::field() !!}
@endif
<div class="wds-page">
  <header class="wds-header">
    <div>
      @if ($title !== null && $title !== '')
        <h1 class="wds-header-heading">{{ $title }}</h1>
      @endif
      @if ($description !== null && $description !== '')
        <p class="wds-header-subheading">{{ $description }}</p>
      @endif
    </div>
    @if (!empty($actions))
      <div class="wds-header-actions">
        {!! $actions !!}
      </div>
    @endif
  </header>
  <div class="wds-page-content">
    {!! $slot !!}
  </div>
</div>
