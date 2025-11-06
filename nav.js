
document.addEventListener('DOMContentLoaded',()=>{
  const nav = document.querySelector('.site_hdr .nav');
  document.getElementById('nav_toggle')?.addEventListener('click',()=>nav.classList.toggle('nav_open'));
  const path = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.site_links a').forEach(a=>{ if(a.getAttribute('href')===path) a.classList.add('active'); });
});
