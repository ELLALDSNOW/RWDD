(function () {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(ent => {
      if (ent.isIntersecting) {
        ent.target.classList.add('inview');
      } else {
        ent.target.classList.remove('inview');
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.section-animate').forEach(el => observer.observe(el));
})();


document.addEventListener("DOMContentLoaded", function() {
  const montage = document.querySelector('.barcode-montage');
  if (montage) {
    let html = '';
    for (let i = 0; i < 120; i++) html += '<span>HIVE</span>';
    montage.innerHTML = html;
  }
});