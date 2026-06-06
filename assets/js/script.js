(function () {
  'use strict';

  // ─── Password Toggle ───
  window.togglePassword = function (inputId, btn) {
    var input = document.getElementById(inputId);
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
  };

  // ─── Navbar Scroll Effect ───
  var navbar = document.querySelector('.navbar');
  if (navbar) {
    var scrollHandler = function () {
      if (window.scrollY > 20) {
        navbar.classList.add('navbar-scrolled');
      } else {
        navbar.classList.remove('navbar-scrolled');
      }
    };
    window.addEventListener('scroll', scrollHandler, { passive: true });
    scrollHandler();
  }

  // ─── Auto-dismiss Alerts ───
  var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
  alerts.forEach(function (alert) {
    setTimeout(function () {
      alert.style.transition = 'opacity 0.3s ease';
      alert.style.opacity = '0';
      setTimeout(function () { alert.remove(); }, 350);
    }, 5000);
  });

  // ─── Toast Notification System ───
  window.showToast = function (message, type) {
    type = type || 'success';
    var container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }

    var icons = { success: 'bi-check-circle-fill', error: 'bi-x-circle-fill', info: 'bi-info-circle-fill' };
    var toast = document.createElement('div');
    toast.className = 'custom-toast toast-' + type;
    toast.innerHTML = '<i class="bi ' + (icons[type] || icons.info) + '"></i> ' + message;
    container.appendChild(toast);

    setTimeout(function () {
      toast.style.animation = 'slideOutRight 0.3s ease forwards';
      setTimeout(function () { toast.remove(); }, 350);
    }, 4000);
  };

  // ─── Animate Counters ───
  var counters = document.querySelectorAll('.animate-counter');
  if (counters.length) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var el = entry.target;
          var target = parseInt(el.getAttribute('data-target'), 10) || 0;
          var duration = 1500;
          var start = 0;
          var step = Math.max(1, Math.floor(target / 30));
          var timer = setInterval(function () {
            start += step;
            if (start >= target) {
              el.textContent = target;
              clearInterval(timer);
            } else {
              el.textContent = start;
            }
          }, duration / 30);
          counterObserver.unobserve(el);
        }
      });
    }, { threshold: 0.5 });
    counters.forEach(function (c) { counterObserver.observe(c); });
  }

  // ─── Scroll Reveal Animations ───
  var revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function (el) { revealObserver.observe(el); });
  }

  // ─── Confirm Modal (replaces native confirm) ───
  window.showConfirm = function (message, callback) {
    var modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.tabIndex = -1;
    modal.innerHTML =
      '<div class="modal-dialog modal-dialog-centered">' +
      '  <div class="modal-content">' +
      '    <div class="modal-header">' +
      '      <h5 class="modal-title"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm</h5>' +
      '      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>' +
      '    </div>' +
      '    <div class="modal-body">' +
      '      <p class="mb-0">' + message + '</p>' +
      '    </div>' +
      '    <div class="modal-footer">' +
      '      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>' +
      '      <button type="button" class="btn btn-danger" id="confirmBtn">Yes, proceed</button>' +
      '    </div>' +
      '  </div>' +
      '</div>';
    document.body.appendChild(modal);
    var bsModal = new bootstrap.Modal(modal);
    modal.querySelector('#confirmBtn').addEventListener('click', function () {
      bsModal.hide();
      if (callback) callback();
    });
    modal.addEventListener('hidden.bs.modal', function () { modal.remove(); });
    bsModal.show();
  };

  // ─── Remove confirm() from delete links and forms ───
  document.addEventListener('click', function (e) {
    var target = e.target.closest('[data-confirm]');
    if (target) {
      e.preventDefault();
      var msg = target.getAttribute('data-confirm') || 'Are you sure?';
      showConfirm(msg, function () {
        if (target.tagName === 'FORM') { target.submit(); }
        else { window.location.href = target.href; }
      });
    }
  });

})();
