<form method="post" action="{{ $action }}">
  {!! $schema->render($state) !!}
  <p>
    <x-webkernel::button type="submit" color="primary">Save</x-webkernel::button>
    <x-webkernel::button href="/billing/invoices" tag="a" color="gray">Cancel</x-webkernel::button>
  </p>
</form>
