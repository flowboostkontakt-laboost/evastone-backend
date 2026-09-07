/* EvaStone — warstwa ruchu (bez zależności).
   Reveal na scroll (stagger), magnetic tilt, spotlight na ciemnym, parallax hero.
   Szanuje prefers-reduced-motion. Nie blokuje treści, jeśli JS/IO nie działa. */
(function () {
  'use strict';
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    // ---- 1. REVEAL na scroll (stagger po rodzeństwie) ----
    var reveals = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));
    // stagger: kolejnym elementom w tym samym rodzicu nadaj --i
    var groups = {};
    reveals.forEach(function (el) {
      var p = el.parentNode;
      var key = groups._id ? groups._id : (groups._id = 0);
      if (!p.__revIdx) p.__revIdx = 0;
      el.style.setProperty('--i', p.__revIdx++);
    });

    if (reduce || !('IntersectionObserver' in window)) {
      reveals.forEach(function (el) { el.classList.add('is-in'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
      var vh = window.innerHeight || document.documentElement.clientHeight;
      reveals.forEach(function (el) {
        // cokolwiek widoczne przy starcie (top nad dolną krawędzią) ujawniamy od razu
        if (el.getBoundingClientRect().top < vh) { el.classList.add('is-in'); }
        else { io.observe(el); }
      });
    }

    if (reduce) return; // reszta to ozdoby ruchu — pomijamy

    // ---- 2. SPOTLIGHT na ciemnych sekcjach ----
    if (fine) {
      document.querySelectorAll('.eva-spot').forEach(function (el) {
        el.addEventListener('pointermove', function (ev) {
          var r = el.getBoundingClientRect();
          el.style.setProperty('--mx', ((ev.clientX - r.left) / r.width * 100) + '%');
          el.style.setProperty('--my', ((ev.clientY - r.top) / r.height * 100) + '%');
          el.classList.add('is-hot');
        });
        el.addEventListener('pointerleave', function () { el.classList.remove('is-hot'); });
      });

      // ---- 3. MAGNETIC TILT ----
      document.querySelectorAll('[data-tilt]').forEach(function (el) {
        var max = parseFloat(el.getAttribute('data-tilt')) || 5;
        el.classList.add('eva-tilt');
        el.addEventListener('pointermove', function (ev) {
          var r = el.getBoundingClientRect();
          var px = (ev.clientX - r.left) / r.width - 0.5;
          var py = (ev.clientY - r.top) / r.height - 0.5;
          el.style.setProperty('--rx', (px * max).toFixed(2) + 'deg');
          el.style.setProperty('--ry', (-py * max).toFixed(2) + 'deg');
        });
        el.addEventListener('pointerleave', function () {
          el.style.setProperty('--rx', '0deg');
          el.style.setProperty('--ry', '0deg');
        });
      });
    }

    // ---- 4. PARALLAX + skala hero ----
    var heroImg = document.querySelector('.eva-hero__img');
    if (heroImg) {
      var ticking = false;
      var onScroll = function () {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(function () {
          var y = window.scrollY || 0;
          var t = Math.min(y, 700);
          heroImg.style.transform = 'scale(' + (1.05 + t / 7000) + ') translateY(' + (t * 0.12) + 'px)';
          ticking = false;
        });
      };
      window.addEventListener('scroll', onScroll, { passive: true });
    }
  });
})();
