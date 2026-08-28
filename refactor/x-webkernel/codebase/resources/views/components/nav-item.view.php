<a href="{{ $href }}" class="webkernel-shell-nav-item{{ !empty($active) ? ' active' : '' }}">
  @if (!empty($icon))
    <x-webkernel::icon :name="$icon" />
  @endif
  <span class="webkernel-shell-nav-label">{!! $slot !!}</span>
</a>
