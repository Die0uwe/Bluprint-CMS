/* Blueprint CMS — Core JS v1.0.0
   Copyright (C) 2026 DieOuwe — GPL-3.0-or-later */
'use strict';
document.querySelectorAll('.cf-nav-link').forEach(link => {
  if (link.href === window.location.href) link.classList.add('active');
});
setTimeout(() => {
  document.querySelectorAll('.cf-alert').forEach(el => {
    el.style.transition = 'opacity .4s';
    el.style.opacity = '0';
    setTimeout(() => el.remove(), 400);
  });
}, 5000);
