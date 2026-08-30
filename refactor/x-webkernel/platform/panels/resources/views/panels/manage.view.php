<div id="w-manage">
  <form
    method="post"
    action="{{ $action }}"
    hx-post="{{ $action }}"
    hx-target="#w-manage"
    hx-swap="outerHTML"
  >
    {!! \Webkernel\Csrf::field() !!}
    <input type="hidden" name="edit_locale" value="{{ $edit_locale }}" />
    {!! $schema !!}
    <div class="w-form-actions">
      <x-webkernel::button type="submit" color="primary">{{ \function_exists('lang') ? lang('panel.save') : 'Save' }}</x-webkernel::button>
    </div>
  </form>
</div>
