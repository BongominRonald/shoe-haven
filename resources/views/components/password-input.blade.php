@props(['disabled' => false])

<div class="position-relative">
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'form-control', 'type' => 'password']) }}
           style="padding-right: 2.5rem;">
    <button type="button" class="btn position-absolute top-50 end-0 translate-middle-y me-2 p-0 border-0 bg-transparent sh-pw-toggle"
            aria-label="Toggle password visibility"
            tabindex="-1" style="z-index: 5; line-height: 1;">
        <i class="bi bi-eye" style="font-size: 1.1rem; color: #6c757d;"></i>
    </button>
</div>

<script>
(function() {
    if (window.__pwToggleBound) return;
    window.__pwToggleBound = true;
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.sh-pw-toggle');
        if (!btn) return;
        var input = btn.closest('.position-relative').querySelector('input');
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
})();
</script>
