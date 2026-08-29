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
<style>
.wds-page { display: flex; flex-direction: column; gap: 1.25rem; color: var(--wds-text); }
.wds-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}
.wds-header-heading {
  font-size: 26px;
  font-weight: 500;
  letter-spacing: -0.02em;
  line-height: 1.2;
  color: var(--wds-text);
}
.wds-header-subheading {
  margin-top: 0.25rem;
  font-size: var(--wds-text-sm);
  color: var(--wds-text-muted);
}
.wds-header-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-shrink: 0;
}
.wds-page-content { min-width: 0; color: var(--wds-text); }
@media (max-width: 640px) {
  .wds-header { flex-direction: column; }
}
</style>
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
