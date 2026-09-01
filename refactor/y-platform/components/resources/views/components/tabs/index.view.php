{{--
  <x-webkernel::tabs>
    <x-webkernel::tabs.item tab="branding" :active="true">Branding</x-webkernel::tabs.item>
    <x-webkernel::tabs.panel tab="branding">...</x-webkernel::tabs.panel>
  </x-webkernel::tabs>
--}}
@props([
  'label' => null,
  'contained' => true,
  'vertical' => false,
  'scrollable' => true,
  'persist_tab' => false,
  'persist_query' => null,
  'id' => null,
  'key' => null,
  'tab_keys' => [],
  'list' => '',
  'slot' => '',
  'name' => '',
  'state' => [],
  'errors' => [],
  'mode' => null,
])
@php
  $attributes = $attributes ?? new \Webkernel\View\AttributeBag([]);
@endphp
<div
  {{ $attributes->class([
    'w-sc-tabs',
    'w-contained' => $contained,
    'w-vertical' => $vertical,
  ]) }}
  data-w-tabs
  @if ($id) id="{{ $id }}" data-tabs-id="{{ $id }}" @endif
  @if ($key) data-tabs-key="{{ $key }}" @endif
  @if ($persist_tab && $id) data-persist-tab="{{ $id }}" @endif
  @if ($persist_query) data-persist-query="{{ $persist_query }}" @endif
  data-scrollable="{{ $scrollable ? 'true' : 'false' }}"
>
  @if (\is_array($tab_keys) && $tab_keys !== [])
    <input type="hidden" value="{{ \htmlspecialchars(\json_encode(\array_values($tab_keys), JSON_UNESCAPED_UNICODE), ENT_QUOTES) }}" data-w-tabs-data />
  @endif
  <nav
    class="w-tabs{{ $contained ? ' w-contained' : '' }}{{ $vertical ? ' w-vertical' : '' }}{{ $scrollable ? '' : ' w-not-scrollable' }}"
    role="tablist"
    aria-label="{{ $label ?? '' }}"
  >
    {!! $list ?? '' !!}
    @if (! $scrollable)
      <div class="w-dropdown w-tabs-overflow" data-w-dropdown data-w-tabs-overflow hidden>
        <div class="w-dropdown-trigger" data-w-dropdown-trigger>
          <button type="button" class="w-tabs-item" data-w-tabs-more role="tab" aria-selected="false">
            <span class="w-tabs-item-label"><x-webkernel::icon name="ellipsis" /></span>
          </button>
        </div>
        <div class="w-dropdown-panel" data-w-dropdown-panel>
          <div class="w-dropdown-list" data-w-tabs-overflow-list></div>
        </div>
      </div>
    @endif
  </nav>
  {!! $slot !!}
</div>
