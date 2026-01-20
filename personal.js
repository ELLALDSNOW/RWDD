
document.addEventListener('DOMContentLoaded', () => {
  const createOrgSidebar = document.getElementById('createOrgSidebar');
  const createPersonalSidebar = document.getElementById('createPersonalSidebar');
  const createOrgBtn = document.getElementById('createOrgBtn'); 
  const orgModal = document.getElementById('orgModal');
  const closeOrgModal = document.getElementById('closeOrgModal');
  const cancelOrgBtn = document.getElementById('cancelOrgBtn');
  const hamburger = document.getElementById('hamburger');
  const dropdown = document.getElementById('dropdown');
  const sidebar = document.getElementById('sidebar');
  const sidebarToggle = document.getElementById('sidebarToggle');
  const darkModeToggle = document.getElementById('darkModeToggle');

  function showModal(mode = 'create', data = {}) {
    const modalTitle = document.getElementById('modalTitle');
    modalTitle.textContent = mode === 'create' ? 'Create Organization' : 'Edit Organization';
    document.getElementById('org_id').value = data.organization_id || '';
    document.getElementById('org_name').value = data.name || '';
    document.getElementById('org_desc').value = data.description || '';
    document.getElementById('org_priority').value = data.priority || 'Medium';
    if (orgModal) orgModal.style.display = 'flex';
   
    try {
      const toggle = document.getElementById('org_password_toggle');
      if (toggle) {
        toggle.onclick = () => {
          const inp = document.getElementById('org_password');
          if (!inp) return;
          const icon = toggle.querySelector('i');
          if (inp.type === 'password') { inp.type = 'text'; if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } }
          else { inp.type = 'password'; if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); } }
        };
      }
    } catch(e) {}
  }
  function showProjectModal(mode='create', data={}){
    const modal = document.getElementById('projectModal');
    if (!modal) return;
    const title = document.getElementById('projectModalTitle');
    title.textContent = mode === 'create' ? 'Create Personal HIVE' : 'Edit Personal HIVE';
    document.getElementById('project_id').value = data.project_id || '';
    document.getElementById('project_name').value = data.name || '';
    document.getElementById('project_desc').value = data.description || '';
    document.getElementById('project_priority').value = data.priority || 'medium';
    document.getElementById('project_due').value = data.due_date ? data.due_date.split(' ')[0] : '';
    modal.style.display = 'flex';
  }
  function hideModal() {
    if (!orgModal) return;
    orgModal.style.display = 'none';
    const form = document.getElementById('orgForm');
    form && form.reset();
    document.getElementById('org_id').value = '';
  }


  createOrgSidebar && createOrgSidebar.addEventListener('click', () => showModal('create'));
  createPersonalSidebar && createPersonalSidebar.addEventListener('click', () => showProjectModal('create'));


  closeOrgModal && closeOrgModal.addEventListener('click', hideModal);
  cancelOrgBtn && cancelOrgBtn.addEventListener('click', hideModal);
  const closeProjectModal = document.getElementById('closeProjectModal');
  const cancelProjectBtn = document.getElementById('cancelProjectBtn');
  const projectModal = document.getElementById('projectModal');
  if (closeProjectModal) closeProjectModal.addEventListener('click', ()=>{ if (projectModal) projectModal.style.display='none'; });
  if (cancelProjectBtn) cancelProjectBtn.addEventListener('click', ()=>{ if (projectModal) projectModal.style.display='none'; });
  window.addEventListener('click', (e) => { if (e.target === orgModal) hideModal(); });
  window.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideModal(); });


  window.addEventListener('click', (e)=>{ if (e.target === projectModal) projectModal.style.display='none'; });


  document.querySelectorAll('.btn-edit-org').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const data = {
        organization_id: btn.dataset.id || '',
        name: btn.dataset.name || '',
        description: btn.dataset.desc || '',
        priority: btn.dataset.priority || ''
      };
      showModal('edit', data);
    });
  });


  if (hamburger && dropdown) {
    hamburger.addEventListener('click', () => dropdown.classList.toggle('show'));
    window.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove('show');
    });
  }


  sidebarToggle && sidebarToggle.addEventListener('click', () => sidebar && sidebar.classList.toggle('show'));


  if (darkModeToggle) {
    darkModeToggle.addEventListener('change', (e) => {
      document.body.classList.toggle('dark-mode', e.target.checked);
      try { localStorage.setItem('dark', e.target.checked ? '1' : '0'); } catch (err) {}
    });
    try {
   
      const stored = (function(){ try { return localStorage.getItem('dark'); } catch(e){} try { return sessionStorage.getItem('dark'); } catch(e){} return null; })();
      if (stored === '1') {
        darkModeToggle.checked = true;
        document.body.classList.add('dark-mode');
      }
    } catch(err){}
  }

  try {
    document.querySelectorAll('form[action*="save_organization.php"], form[action*="delete_organization.php"]').forEach(form => {
      if (form.dataset.noAjax === 'true') return;
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(form);
        try {
          const res = await fetch(form.action, { method: form.method || 'POST', body: fd });
          if (!res.ok) throw new Error('Request failed');
         
          const orgModal = document.getElementById('orgModal'); if (orgModal) orgModal.style.display = 'none';
          window.location.reload();
        } catch (err) {
          console.error('AJAX submit failed, falling back to normal submit', err);
          form.submit();
        }
      });
    });
 
    const projForm = document.getElementById('projectForm');
    if (projForm) {
      projForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const fd = new FormData(projForm);
        try {
          const res = await fetch(projForm.action, { method: projForm.method || 'POST', body: fd, headers: { 'X-Requested-With':'XMLHttpRequest' } });
          if (!res.ok) throw new Error('Request failed');
          const json = await res.json().catch(()=>null);
          const projectModalEl = document.getElementById('projectModal'); if (projectModalEl) projectModalEl.style.display='none';
       
          if (json && json.ok && json.project_id) {
            window.location.href = 'personalhive.php?project_id=' + encodeURIComponent(json.project_id);
          } else {
            window.location.reload();
          }
        } catch(err){ console.error('Project save failed, falling back', err); projForm.submit(); }
      });
    }
  } catch (err) {
    console.error('Error attaching AJAX handlers on personal.php', err);
  }


  document.querySelectorAll('.card-btn.edit').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const data = {
        project_id: btn.dataset.id || '',
        name: btn.dataset.name || '',
        description: btn.dataset.desc || '',
        priority: btn.dataset.priority || 'medium'
      };
      showProjectModal('edit', data);
    });
  });

 
  document.querySelectorAll('form[action*="delete_project.php"]').forEach(form => {
    form.addEventListener('submit', async function(e){
      e.preventDefault();
      if (!confirm('Delete this project?')) return;
      const fd = new FormData(form);
      try {
        const res = await fetch(form.action, { method: form.method || 'POST', body: fd, headers: { 'X-Requested-With':'XMLHttpRequest' } });
        if (!res.ok) throw new Error('Request failed');
        const json = await res.json().catch(()=>null);
        if (json && json.ok) {
          window.location.reload();
        } else {
          window.location.reload();
        }
      } catch(err) { console.error('Delete project failed', err); form.submit(); }
    });
  });
});