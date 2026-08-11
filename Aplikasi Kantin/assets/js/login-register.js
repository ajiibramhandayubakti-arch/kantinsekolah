// E-Kantin Sekolah — small, dependency-free UX helpers for auth pages

document.addEventListener('DOMContentLoaded', function () {
  // 1) Show/hide password
  document.querySelectorAll('.toggle-pass').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.dataset.target);
      if (!input) return;
      var isHidden = input.type === 'password';
      input.type = isHidden ? 'text' : 'password';
      btn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
      btn.innerHTML = isHidden ? eyeOffIcon() : eyeIcon();
    });
  });

  // 2) Loading state on submit (guards against double-submit on slow connections)
  document.querySelectorAll('form[data-loading-form]').forEach(function (form) {
    form.addEventListener('submit', function () {
      var btn = form.querySelector('button[type="submit"]');
      if (!btn) return;
      btn.disabled = true;
      var spinner = btn.querySelector('.spinner');
      var label = btn.querySelector('.btn-label');
      if (spinner) spinner.style.display = 'inline-block';
      if (label) label.textContent = form.dataset.loadingText || 'Memproses...';
    });
  });

  // 3) Keep the selected role card visually in sync (native :checked CSS already
  //    handles the styling; this just moves focus for keyboard/screen-reader users)
  document.querySelectorAll('.role-group input[type="radio"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
      document.querySelectorAll('.role-card').forEach(function (card) {
        card.classList.remove('is-active');
      });
      var card = radio.nextElementSibling;
      if (card) card.classList.add('is-active');
    });
  });
});

function eyeIcon() {
  return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>';
}
function eyeOffIcon() {
  return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><path d="M6.61 6.61C3.06 8.9 1 12 1 12s4 8 11 8a9.26 9.26 0 0 0 5.39-1.61"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
}