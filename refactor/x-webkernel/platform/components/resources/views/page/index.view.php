{{--
  <x-webkernel::page title="..." description="...">
    @slot('actions') ... @endslot
    body
  </x-webkernel::page>
--}}
@props([
  'title' => null,
  'description' => null,
])
<div class="webkernel-shell-page">
  <header class="webkernel-shell-page-header">
    <div>
      @if ($title !== null && $title !== '')
        <h1 class="webkernel-shell-page-title">{{ $title }}</h1>
      @endif
      @if ($description !== null && $description !== '')
        <p class="webkernel-shell-page-desc">{{ $description }}</p>
      @endif
    </div>
    @if (!empty($actions))
      <div class="webkernel-shell-page-actions">
        {!! $actions !!}
      </div>
    @endif
  </header>
  <div class="webkernel-shell-page-body">
    {!! $slot !!}
  </div>
</div>
