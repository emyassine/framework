@once('wds.page')
@push('styles')
<style>
.wds-page { display: flex; flex-direction: column; gap: 1.25rem; }
.wds-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
}
.wds-header-heading {
  font-size: var(--wds-text-2xl);
  font-weight: 700;
  letter-spacing: -0.03em;
  line-height: 1.2;
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
.wds-page-content { min-width: 0; }
@media (max-width: 640px) {
  .wds-header { flex-direction: column; }
}
</style>
@endpush
@endonce
