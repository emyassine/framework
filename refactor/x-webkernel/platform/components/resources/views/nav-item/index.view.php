<a href="{{ $href }}" class="wds-sidebar-item{{ !empty($active) ? ' wds-active' : '' }}">
  @if (!empty($icon))
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="wds-sidebar-item-label">{!! $slot !!}</span>
</a>
