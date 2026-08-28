{{--
  <x-webkernel::page title="..." description="...">
    @slot('actions') ... @endslot
    body
  </x-webkernel::page>
--}}
<div class="wks-page">
  <header class="wks-page-header">
    <div>
      <h1 class="wks-page-title">{{ $title ?? '' }}</h1>
      @if (!empty($description))
        <p class="wks-page-desc">{{ $description }}</p>
      @endif
    </div>
    @if (!empty($actions))
      <div class="wks-page-actions">
        {!! $actions !!}
      </div>
    @endif
  </header>
  <div class="wks-page-body">
    {!! $slot !!}
  </div>
</div>
