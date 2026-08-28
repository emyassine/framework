{{--
  <x-webkernel::page title="..." description="...">
    @slot('actions') ... @endslot
    body
  </x-webkernel::page>
--}}
<div class="webkernel-shell-page">
  <header class="webkernel-shell-page-header">
    <div>
      <h1 class="webkernel-shell-page-title">{{ $title ?? '' }}</h1>
      @if (!empty($description))
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
