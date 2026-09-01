@props([
  'href' => null,
])
<tr class="w-ta-row" @if (!empty($href)) onclick="window.location.href={{ json_encode($href) }}" style="cursor:pointer" @endif>
  {!! $slot !!}
</tr>
