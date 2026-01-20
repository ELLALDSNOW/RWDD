
function getOrg() {

  const main = document.querySelector('main.org-main');
  if (!main) return null;
  return {
    id: main.dataset.orgId,
    organization_id: main.dataset.orgId
  };
}

async function saveOrg(org) {

  try {
    const fd = new FormData();
    if (org) {

      if (typeof org === 'object' && !(org instanceof FormData)) {
        for (const k of Object.keys(org)) {
          if (org[k] !== undefined && org[k] !== null) fd.append(k, org[k]);
        }
      } else if (org instanceof FormData) {
        for (const pair of org.entries()) fd.append(pair[0], pair[1]);
      }
    }
    const res = await fetch('api/save_organization.php', { method: 'POST', body: fd });
    if (!res.ok) throw new Error('Save organization failed');

    const em = document.getElementById('editModal'); if (em) em.style.display = 'none';
    window.location.reload();
  } catch (err) {
    console.error(err);
    alert('Failed to save organization');
  }
}
function deleteOrg(orgId) {
  if (!orgId) return;
  (async () => {
    try {
      const fd = new URLSearchParams();
      fd.append('org_id', orgId);
      const res = await fetch('api/delete_organization.php', { method: 'POST', body: fd });
      if (!res.ok) throw new Error('Delete organization failed');

      window.location.reload();
    } catch (err) {
      console.error(err);
      alert('Failed to delete organization');
    }
  })();
}
function formatDate(date) {
  if (!date) return '-';
  const d = new Date(date);
  return d.toLocaleDateString('en-GB');
}
function getToday() {
  const d = new Date();
  return d.toLocaleDateString('en-GB');
}
function daysLeft(due) {
  if (!due) return '-';
  const now = new Date();
  const dueDate = new Date(due);
  const diff = dueDate - now;
  if (isNaN(diff)) return '-';
  const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
  return days > 0 ? days + ' Days Left' : 'Due!';
}
function showModal(id) {
  document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}


function renderOrg() {
  
  return; 
}


function renderProjects() {
    return; 
}


document.addEventListener('DOMContentLoaded', function() {
  
    document.querySelectorAll('.project-card').forEach(card => {
        const projectId = card.dataset.projectId;
        if (!projectId) return;
        
      
        const fileInput = card.querySelector('.fileInput');
        const uploadBtn = card.querySelector('.uploadBtn');
        if (fileInput && uploadBtn) {
            uploadBtn.addEventListener('click', e => {
                fileInput.click();
                e.stopPropagation();
            });
            fileInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) {
                    const formData = new FormData();
                    formData.append('project_id', projectId);
                    formData.append('image', file);
                    fetch('api/update_project_image.php', {
                        method: 'POST',
                        body: formData
                    }).then(res => {
                        if (res.ok) window.location.reload();
                        else alert('Failed to update image');
                    });
                }
            });
        }
    });
});


function updateProject(projectId, data) {
    const formData = new FormData();
    for (const [key, value] of Object.entries(data)) {
        if (value !== null && value !== undefined) formData.append(key, value);
    }
    formData.append('project_id', projectId);
    
    return fetch('api/save_project.php', {
        method: 'POST',
        body: formData
    }).then(res => {
        if (res.redirected) window.location.href = res.url;
        else if (!res.ok) throw new Error('Update failed');
        return res;
    });
}


function showEditProjectModal(project, isNew) {
    console.log('showEditProjectModal called', { isNew });
    const main = document.querySelector('main.org-main');
    const orgId = main ? main.dataset.orgId : null;
    if (!orgId) {
        console.error('No organization ID found');
        return;
    }

    let modal = document.getElementById('editModal');
    let modalBody = document.getElementById('editModalBody');
    
    if (!modal) {
        console.log('Creating missing modal');
        modal = document.createElement('div');
        modal.id = 'editModal';
        modal.className = 'modal';
        modal.style.cssText = 'display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center;';
        
        modal.innerHTML = `
            <div class="modal-content scrollable-modal" style="background:white; padding:32px; border-radius:16px; width:90%; max-width:800px; position:relative;">
                <button class="close" onclick="document.getElementById('editModal').style.display='none'" style="position:absolute; right:16px; top:16px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
                <div id="editModalBody"></div>
            </div>
        `;
        document.body.appendChild(modal);
        modalBody = modal.querySelector('#editModalBody');
    }


    if (isNew) {
        project = {
            name: '',
            description: '',
            image: null,
            startDate: '',
            dueDate: ''
        };
    }

    modalBody.innerHTML = `
    <div class="org-header-section modal-header">
      <div class="org-img-wrap">
        <div class="hexagon hexagon-md">
          <img src="${project.image || 'https://via.placeholder.com/140x80?text=No+Image'}" alt="Project Image">
        </div>
      </div>
      <div class="org-info">
        <div class="org-title-edit">
          <h1>${project.name}</h1>
          <button class="edit-btn" id="editProjectNameBtn" title="Edit Project Name"><i class="fa fa-pen"></i></button>
        </div>
        <div class="org-desc-edit">
          <p>${project.description || ''}</p>
          <button class="edit-btn" id="editProjectDescBtn" title="Edit Project Description"><i class="fa fa-pen"></i></button>
        </div>
        <div class="org-date">
          <span>Created: ${formatDate(project.dateCreated)}</span>
        </div>
        <div class="org-actions">
          <button class="org-btn add" id="addTaskBtn"><i class="fa fa-plus"></i> Add New Task</button>
          <button class="org-btn edit" id="editProjectBtn"><i class="fa fa-pen"></i> Edit Project</button>
          <button class="org-btn delete" id="deleteProjectBtn"><i class="fa fa-trash"></i> Delete Project</button>
        </div>
      </div>
    </div>
    <div class="hive-summary">
      <div class="hive-stats">
        <span>Tasks: <b>${totalTasks}</b></span>
        <span>Completed: <b>${completed}</b></span>
        <span>In-progress: <b>${inProgress}</b></span>
        <span>Uninitiated: <b>${uninitiated}</b></span>
      </div>
      <div class="hive-dates">
        <div>Project Start Date: <b>${startDate}</b></div>
        <div>Project Due Date: <b>${dueDate}</b></div>
      </div>
      <div class="hive-progress-bar">
        <div class="hive-progress" style="width:${percent}%;"></div>
      </div>
      <div class="hive-progress-labels">
        <span class="hive-progress-completed">${percent}% completed</span>
        <span class="hive-progress-days"><i class="fa fa-calendar"></i> ${daysLeft(dueDate)}</span>
      </div>
    </div>
    <section class="hive-section">
      <h2>In Progress</h2>
      <div class="hive-tasks-row" id="inProgressTasks"></div>
    </section>
    <section class="hive-section">
      <h2>Completed</h2>
      <div class="hive-tasks-row" id="completedTasks"></div>
    </section>
    <section class="hive-section">
      <h2>Uninitiated</h2>
      <div class="hive-tasks-row" id="uninitiatedTasks"></div>
    </section>
  `;

  renderTaskSection(project, 'In Progress', 'inProgressTasks');
  renderTaskSection(project, 'Completed', 'completedTasks');
  renderTaskSection(project, 'Uninitiated', 'uninitiatedTasks');

  document.getElementById('addTaskBtn').onclick = () => showEditTaskModal(project, null);
  document.getElementById('editProjectBtn').onclick = () => showEditProjectModal(project);
  document.getElementById('deleteProjectBtn').onclick = () => {
    if (confirm(`Once deleted, you can't bring the project back. Delete "${project.name}" from "${org.name}"?`)) {
      deleteProject(project.id);
      closeModal('projectModal');
    }
  };
  document.getElementById('editProjectNameBtn').onclick = () => showEditProjectFieldModal(project, 'name');
  document.getElementById('editProjectDescBtn').onclick = () => showEditProjectFieldModal(project, 'desc');
}


function renderTaskSection(project, status, containerId) {
  const row = document.getElementById(containerId);
  row.innerHTML = '';
  if (!project.tasks) project.tasks = [];
  project.tasks.filter(t => t.status === status).forEach(task => {
    const cardDiv = document.createElement('div');
    let cardClass = 'hive-task-card';
    if (status === 'Completed') cardClass += ' yellow';
    else if (status === 'In Progress') cardClass += ' yellowpale';
    else if (status === 'Uninitiated') cardClass += ' black';
    cardDiv.className = cardClass;
    cardDiv.innerHTML = `
      <div class="hive-task-title">${task.name}</div>
      <div class="hive-task-meta">Priority: <b>${task.priority}</b></div>
      <div class="hive-task-meta">Deadline: <b>${task.deadline || '-'}</b></div>
      <div class="hive-task-meta">Status: <b>${task.status}</b></div>
    `;
    cardDiv.onclick = () => showEditTaskModal(project, task);
    row.appendChild(cardDiv);
  });
}


function showEditProjectModal(project, isNew) {
  console.log('showEditProjectModal called', { isNew });
  const main = document.querySelector('main.org-main');
  const orgId = main ? main.dataset.orgId : null;
  if (!orgId) {
    console.error('No organization ID found');
    return;
  }


  let modal = document.getElementById('editModal');
  let modalBody = document.getElementById('editModalBody');
  
  if (!modal) {
    console.log('Creating missing modal');
    modal = document.createElement('div');
    modal.id = 'editModal';
    modal.className = 'modal';
    modal.style.display = 'none';
    modal.style.position = 'fixed';
    modal.style.zIndex = '1000';
    modal.style.left = '0';
    modal.style.top = '0';
    modal.style.width = '100%';
    modal.style.height = '100%';
    modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
    modal.style.display = 'none';
    modal.style.alignItems = 'center';
    modal.style.justifyContent = 'center';

    modal.innerHTML = `
      <div class="modal-content scrollable-modal" style="background:white; padding:32px; border-radius:16px; width:90%; max-width:800px; position:relative;">
        <button class="close" onclick="document.getElementById('editModal').style.display='none'" style="position:absolute; right:16px; top:16px; background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
        <div id="editModalBody"></div>
      </div>
    `;
    document.body.appendChild(modal);
    modalBody = modal.querySelector('#editModalBody');
  }
  modalBody.innerHTML = `
    <form class="edit-modal-form" id="editProjectForm">
      <div style="display:grid; grid-template-columns:1fr 140px; gap:12px; align-items:start;">
        <div>
          <label style="display:block; font-weight:600; margin-bottom:6px;">Project Name</label>
          <input type="text" id="editProjectName" value="${project.name}" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:10px;">
          <label style="display:block; font-weight:600; margin-bottom:6px;">Description</label>
          <textarea id="editProjectDesc" style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; height:100px;">${project.description || ''}</textarea>
          <div style="display:flex; gap:8px; margin-top:10px;">
            <div style="flex:1">
              <label style="display:block; font-weight:600; margin-bottom:6px;">Due Date</label>
              <input type="date" id="editProjectDue" value="${project.dueDate || ''}" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
            </div>
            <div style="width:120px">
              <label style="display:block; font-weight:600; margin-bottom:6px;">Priority</label>
              <select id="editProjectPriority" style="width:100%; padding:8px; border-radius:6px; border:1px solid #ddd;">
                <option value="high" ${project.priority === 'high' ? 'selected' : ''}>High</option>
                <option value="medium" ${project.priority === 'medium' || !project.priority ? 'selected' : ''}>Medium</option>
                <option value="low" ${project.priority === 'low' ? 'selected' : ''}>Low</option>
              </select>
            </div>
          </div>
        </div>
        <div style="text-align:center;">
          <div style="width:120px; height:120px; margin:0 auto 8px; border-radius:10px; overflow:hidden; background:#f6f6f6; display:flex; align-items:center; justify-content:center;">
            <img src="${project.image || 'https://via.placeholder.com/120'}" id="editProjectImgPreview" style="width:100%; height:100%; object-fit:cover;">
          </div>
          <input type="file" id="editProjectImgInput" accept="image/*" style="display:block; margin:0 auto 8px;">
          <button type="button" class="img-upload-btn" id="editProjectImgBtn2" style="display:block; margin:0 auto; padding:8px 12px; border-radius:8px;">Upload Image</button>
        </div>
      </div>
      <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" class="cancel" id="cancelEditProjectBtn2" style="background:#fff; border:1px solid #ccc; padding:8px 14px; border-radius:8px; cursor:pointer;">Cancel</button>
        <button type="submit" class="save" style="background:#ffe100; border:none; padding:8px 16px; border-radius:8px; cursor:pointer; font-weight:700;">Save</button>
      </div>
    </form>
  `;

  const imgInput = document.getElementById('editProjectImgInput');
  const imgBtn = document.getElementById('editProjectImgBtn2');
  if (imgBtn) imgBtn.onclick = () => imgInput.click();
  if (imgInput) {
    imgInput.onchange = e => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
          document.getElementById('editProjectImgPreview').src = ev.target.result;
          project.image = ev.target.result;
        };
        reader.readAsDataURL(file);
      }
    };
  }
  showModal('editModal');
  document.getElementById('editProjectForm').onsubmit = async function(e) {
    e.preventDefault();

  const name = document.getElementById('editProjectName').value.trim();
  const description = document.getElementById('editProjectDesc').value;

  const startDate = '';
  const dueDate = document.getElementById('editProjectDue').value;
    const imgPreview = document.getElementById('editProjectImgPreview');

    const fd = new FormData();
    fd.append('name', name);
    fd.append('description', description);
    fd.append('due_date', dueDate);

    const fileInput = document.getElementById('editProjectImgInput');
    if (fileInput && fileInput.files && fileInput.files[0]) fd.append('image', fileInput.files[0]);
 
    const main = document.querySelector('main.org-main');
    const orgId = main ? main.dataset.orgId : null;
    if (orgId) fd.append('organization_id', orgId);

    if (!isNew && project && (project.project_id || project.id)) {
      fd.append('project_id', project.project_id || project.id);
    }

    try {
      const res = await fetch('api/save_project.php', { method: 'POST', body: fd });
      if (res.redirected) {
        window.location.href = res.url;
        return;
      }
  
      window.location.reload();
    } catch (err) {
      console.error(err);
      alert('Failed to save project');
    }
  };
  document.getElementById('cancelEditProjectBtn2').onclick = () => closeModal('editModal');
}


function showEditProjectFieldModal(project, field) {
  const org = getOrg();
  const modalBody = document.getElementById('editModalBody');
  let html = `<h2>Edit Project ${field === 'name' ? 'Name' : 'Description'}</h2>`;
  if (field === 'name') {
    html += `<div><label>Name:</label><input type="text" id="editProjectName" value="${project.name}"></div>`;
  } else {
    html += `<div><label>Description:</label><textarea id="editProjectDesc">${project.description || ''}</textarea></div>`;
  }
  html += `<div style="margin-top:18px;">
    <button id="saveEditProjectFieldBtn" class="org-btn add">Save</button>
    <button id="cancelEditProjectFieldBtn" class="org-btn delete">Cancel</button>
  </div>`;
  modalBody.innerHTML = html;
  showModal('editModal');
  document.getElementById('saveEditProjectFieldBtn').onclick = () => {
    let orgs = JSON.parse(localStorage.getItem('organizationHives') || '[]');
    let orgIdx = orgs.findIndex(o => String(o.id) === String(org.id));
    if (orgIdx !== -1) {
      let projects = orgs[orgIdx].projects || [];
      let idx = projects.findIndex(p => String(p.id) === String(project.id));
      if (idx !== -1) {
        if (field === 'name') projects[idx].name = document.getElementById('editProjectName').value;
        else projects[idx].description = document.getElementById('editProjectDesc').value;
        orgs[orgIdx].projects = projects;
        localStorage.setItem('organizationHives', JSON.stringify(orgs));
      }
    }
    closeModal('editModal');
    renderProjects();
  };
  document.getElementById('cancelEditProjectFieldBtn').onclick = () => closeModal('editModal');
}


function showEditTaskModal(project, task) {
  const org = getOrg();
  let isNew = !task;
  if (!task) {
    task = {
      id: Date.now() + Math.random(),
      name: '',
      priority: 'High',
      timeSpent: '0h 0min',
      deadline: '',
      status: 'In Progress',
      created: new Date().toISOString(),
      content: '',
      files: []
    };
  }
  const modalBody = document.getElementById('taskModalBody');
  modalBody.innerHTML = `
    <form class="edit-modal-form" id="editTaskForm">
      <div class="task-modal-header">
        <input type="text" id="taskName" class="task-modal-title" value="${task.name}" placeholder="Task Name">
        <select id="taskPriority">
          <option value="High" ${task.priority === 'High' ? 'selected' : ''}>High</option>
          <option value="Medium" ${task.priority === 'Medium' ? 'selected' : ''}>Medium</option>
          <option value="Low" ${task.priority === 'Low' ? 'selected' : ''}>Low</option>
        </select>
      </div>
      <div class="task-modal-meta">
        <div>Deadline: <input type="date" id="taskDeadline" value="${task.deadline || ''}"></div>
        <div>Status: 
          <select id="taskStatus">
            <option value="In Progress" ${task.status === 'In Progress' ? 'selected' : ''}>In Progress</option>
            <option value="Uninitiated" ${task.status === 'Uninitiated' ? 'selected' : ''}>Uninitiated</option>
            <option value="Completed" ${task.status === 'Completed' ? 'selected' : ''}>Completed</option>
          </select>
        </div>
        <div>Time Spent: <span id="taskTimeSpent">${calcTimeSpent(task.created)}</span></div>
      </div>
      <div class="richtext-toolbar" id="richtextToolbar">
        <button type="button" data-cmd="h1" title="Heading 1"><i class="fa fa-heading"></i>H1</button>
        <button type="button" data-cmd="h3" title="Heading 3"><i class="fa fa-heading"></i>H3</button>
        <button type="button" data-cmd="p" title="Paragraph"><i class="fa fa-paragraph"></i></button>
        <button type="button" data-cmd="ul" title="Bulleted List"><i class="fa fa-list-ul"></i></button>
        <button type="button" data-cmd="ol" title="Numbered List"><i class="fa fa-list-ol"></i></button>
        <button type="button" data-cmd="checkbox" title="Checkbox"><i class="fa fa-check-square"></i></button>
        <button type="button" data-cmd="bold" title="Bold"><i class="fa fa-bold"></i></button>
        <button type="button" data-cmd="italic" title="Italic"><i class="fa fa-italic"></i></button>
      </div>
      <div class="richtext-content" id="taskContent" contenteditable="true" spellcheck="true" aria-label="Task Content">${task.content || ''}</div>
      <div class="task-modal-files">
        <label>Attachments:</label>
        <input type="file" id="taskFileInput" multiple>
        <div class="task-modal-files-list" id="taskFilesList"></div>
      </div>
      <div class="modal-actions">
        <button type="submit" class="save">Save</button>
        ${!isNew ? `<button type="button" class="delete" id="deleteTaskBtn">Delete</button>` : ''}
        <button type="button" class="cancel" id="cancelTaskBtn">Cancel</button>
      </div>
    </form>
  `;
  setupRichTextEditor('richtextToolbar', 'taskContent');
  renderTaskFiles(task);


  document.getElementById('taskFileInput').addEventListener('change', function(e) {
    const files = Array.from(e.target.files);
    files.forEach(file => {
      const reader = new FileReader();
      reader.onload = ev => {
        if (!task.files) task.files = [];
        task.files.push({ name: file.name, data: ev.target.result });
        renderTaskFiles(task);
      };
      reader.readAsDataURL(file);
    });
    e.target.value = '';
  });


  document.getElementById('editTaskForm').onsubmit = function(e) {
    e.preventDefault();
    task.name = document.getElementById('taskName').value;
    task.priority = document.getElementById('taskPriority').value;
    task.deadline = document.getElementById('taskDeadline').value;
    task.status = document.getElementById('taskStatus').value;
    task.content = document.getElementById('taskContent').innerHTML;

    let orgs = JSON.parse(localStorage.getItem('organizationHives') || '[]');
    let orgIdx = orgs.findIndex(o => String(o.id) === String(org.id));
    if (orgIdx !== -1) {
      let projects = orgs[orgIdx].projects || [];
      let projIdx = projects.findIndex(p => String(p.id) === String(project.id));
      if (projIdx !== -1) {
        let tasks = projects[projIdx].tasks || [];
        let taskIdx = tasks.findIndex(t => String(t.id) === String(task.id));
        if (isNew || taskIdx === -1) {
          tasks.push(task);
        } else {
          tasks[taskIdx] = task;
        }
        projects[projIdx].tasks = tasks;
        orgs[orgIdx].projects = projects;
        localStorage.setItem('organizationHives', JSON.stringify(orgs));
      }
    }
    closeModal('taskModal');
   
    let orgLatest = getOrg();
    let projectLatest = orgLatest.projects.find(p => String(p.id) === String(project.id));
    renderProjectModal(projectLatest || project);
  };


  if (!isNew) {
    document.getElementById('deleteTaskBtn').onclick = () => {
      if (confirm('Delete this task?')) {
        let orgs = JSON.parse(localStorage.getItem('organizationHives') || '[]');
        let orgIdx = orgs.findIndex(o => String(o.id) === String(org.id));
        if (orgIdx !== -1) {
          let projects = orgs[orgIdx].projects || [];
          let projIdx = projects.findIndex(p => String(p.id) === String(project.id));
          if (projIdx !== -1) {
            let tasks = projects[projIdx].tasks || [];
            tasks = tasks.filter(t => String(t.id) !== String(task.id));
            projects[projIdx].tasks = tasks;
            orgs[orgIdx].projects = projects;
            localStorage.setItem('organizationHives', JSON.stringify(orgs));
          }
        }
        closeModal('taskModal');
        let orgLatest = getOrg();
        let projectLatest = orgLatest.projects.find(p => String(p.id) === String(project.id));
        renderProjectModal(projectLatest || project);
      }
    };
  }
  document.getElementById('cancelTaskBtn').onclick = () => closeModal('taskModal');
  showModal('taskModal');
}


function renderTaskFiles(task) {
  const filesList = document.getElementById('taskFilesList');
  if (!filesList) return;
  filesList.innerHTML = '';
  if (task.files && task.files.length) {
    task.files.forEach((file, idx) => {
      const div = document.createElement('div');
      div.className = 'task-modal-file-item';
      div.innerHTML = `
        <a href="${file.data}" download="${file.name}" target="_blank"><i class="fa fa-paperclip"></i> ${file.name}</a>
        <span class="task-modal-file-remove" title="Remove File">&times;</span>
      `;
      div.querySelector('.task-modal-file-remove').onclick = (e) => {
        task.files.splice(idx, 1);
        renderTaskFiles(task);
        e.stopPropagation();
      };
      filesList.appendChild(div);
    });
  }
}


(() => {
  const editOrgImgBtn = document.getElementById('editOrgImgBtn');
  const orgImageInput = document.getElementById('orgImageInput');
  if (editOrgImgBtn && orgImageInput) {
    editOrgImgBtn.onclick = () => orgImageInput.click();
    orgImageInput.onchange = e => {
      const org = getOrg();
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = ev => {
          org.image = ev.target.result;
          saveOrg(org);
          renderOrg();
        };
        reader.readAsDataURL(file);
      }
    };
  }


  const editOrgNameBtn = document.getElementById('editOrgNameBtn');
  if (editOrgNameBtn) {
    editOrgNameBtn.onclick = () => {
      const org = getOrg();
      showEditOrgModal(org, 'name');
    };
  }
  const editOrgDescBtn = document.getElementById('editOrgDescBtn');
  if (editOrgDescBtn) {
    editOrgDescBtn.onclick = () => {
      const org = getOrg();
      showEditOrgModal(org, 'desc');
    };
  }


  const addProjectBtn = document.getElementById('addProjectBtn');
  if (addProjectBtn) {
    addProjectBtn.onclick = () => showEditProjectModal({
      id: Date.now() + Math.random(),
      name: '',
      description: '',
      image: "https://via.placeholder.com/150",
      dateCreated: new Date().toISOString(),
      startDate: '',
      dueDate: '',
      tasks: []
    }, true);
  }


  const editOrgBtn = document.getElementById('editOrgBtn');
  if (editOrgBtn) {
    editOrgBtn.onclick = () => {
      const em = document.getElementById('editModal');
      if (em) em.style.display = 'flex';
    };
  }


  const closeEditModalBtn = document.getElementById('closeEditModal');
  if (closeEditModalBtn) closeEditModalBtn.onclick = () => {
    const em = document.getElementById('editModal');
    if (em) em.style.display = 'none';
  };
  const cancelEditOrgBtn2 = document.getElementById('cancelEditOrgBtn2');
  if (cancelEditOrgBtn2) cancelEditOrgBtn2.onclick = () => {
    const em = document.getElementById('editModal');
    if (em) em.style.display = 'none';
  };

  const deleteOrgBtn = document.getElementById('deleteOrgBtn');
  if (deleteOrgBtn) deleteOrgBtn.onclick = () => {
    const org = getOrg();
    if (confirm(`Once deleted, you can't bring the organization back. Delete "${org.name}"?`)) {
  
      deleteOrg(org.id);
    }
  };

 
  window.deleteProject = function(projectId) {
    if (!projectId) return;
    (async () => {
      try {
        const fd = new FormData(); fd.append('project_id', projectId);
        const res = await fetch('api/delete_project.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const text = await res.text();
        console.log('delete_project response', res.status, text);
        if (!res.ok) throw new Error(text || 'Delete failed');
        window.location.reload();
      } catch (err) {
        console.error('Failed to delete project', err);
        alert('Failed to delete project: ' + (err.message || err));
      }
    })();
  };

  
  const closeProjectModalBtn = document.getElementById('closeProjectModal');
  if (closeProjectModalBtn) closeProjectModalBtn.onclick = () => closeModal('projectModal');
  if (closeEditModalBtn) closeEditModalBtn.onclick = () => closeModal('editModal');
  const closeTaskModalBtn = document.getElementById('closeTaskModal');
  if (closeTaskModalBtn) closeTaskModalBtn.onclick = () => closeModal('taskModal');


  const closeEditOrgBtnStatic = document.getElementById('closeEditOrgModal');
  if (closeEditOrgBtnStatic) closeEditOrgBtnStatic.onclick = () => {
    const m = document.getElementById('editOrgModal'); if (m) m.style.display = 'none';
  };
  const cancelEditOrgStatic = document.getElementById('cancelEditOrgBtn2');
  if (cancelEditOrgStatic) cancelEditOrgStatic.onclick = () => {
    const m = document.getElementById('editOrgModal'); if (m) m.style.display = 'none';
  };

 
  const editOrgModalEl = document.getElementById('editOrgModal');
  if (editOrgModalEl) {
    const toggleBtn = editOrgModalEl.querySelector('#edit_org_password_toggle');
    const pwdInput = editOrgModalEl.querySelector('#edit_org_password');
    if (toggleBtn && pwdInput) {
      toggleBtn.addEventListener('click', function() {
        const icon = this.querySelector('i');
        if (pwdInput.type === 'password') {
          pwdInput.type = 'text';
          if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
        } else {
          pwdInput.type = 'password';
          if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
        }
      });
    }
  }


  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      const m = document.getElementById('editOrgModal'); if (m && m.style.display === 'flex') m.style.display = 'none';
    }
  });


  try {
    document.querySelectorAll('form[action*="save_organization.php"], form[action*="delete_organization.php"], form[action*="delete_project.php"]').forEach(form => {
  
      if (form.dataset.noAjax === 'true') return;
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const fd = new FormData(form);
        try {
          const res = await fetch(form.action, { method: form.method || 'POST', body: fd, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          if (!res.ok) throw new Error('Request failed');
       
          const em = document.getElementById('editModal'); if (em) em.style.display = 'none';
          window.location.reload();
        } catch (err) {
          console.error('AJAX form submit failed, falling back to normal submit', err);
        
          form.submit();
        }
      });
    });
  } catch (err) {
    console.error('Error attaching AJAX form handlers', err);
  }
})();


function showEditOrgModal(org, field) {
  const modalBody = document.getElementById('editModalBody');

  let html = `<form id="editOrgForm" method="post" action="api/save_organization.php">
    <input type="hidden" name="org_id" value="${org.organization_id || org.id || ''}">
    <label for="edit_org_name">Name</label>
    <input id="edit_org_name" name="name" type="text" required value="${org.name || ''}">
    <label for="edit_org_desc">Description</label>
    <textarea id="edit_org_desc" name="description" rows="3">${org.description || ''}</textarea>
    <label for="edit_org_priority">Priority</label>
    <select id="edit_org_priority" name="priority">
      <option value="Heavy"${org.priority === 'Heavy' ? ' selected' : ''}>Heavy</option>
      <option value="Medium"${!org.priority || org.priority === 'Medium' ? ' selected' : ''}>Medium</option>
      <option value="Light"${org.priority === 'Light' ? ' selected' : ''}>Light</option>
    </select>
    <label for="edit_org_password">Organization password</label>
    <div style="position:relative; display:flex; align-items:center;">
      <input id="edit_org_password" name="org_password" type="password" placeholder="Leave blank to keep current password">
  <button type="button" id="edit_org_password_toggle" class="pwd-toggle" aria-label="Show password"><i class="fa fa-eye"></i></button>
    </div>
    <div class="modal-actions" style="margin-top:12px;">
      <button type="button" class="org-btn delete" id="cancelEditOrgBtn2">Cancel</button>
      <button class="org-btn add" type="submit">Save</button>
    </div>
  </form>`;
  modalBody.innerHTML = html;
  showModal('editModal');
  document.getElementById('cancelEditOrgBtn2').onclick = () => closeModal('editModal');

  const toggle = document.getElementById('edit_org_password_toggle');
  if (toggle) {
    toggle.addEventListener('click', () => {
      const inp = document.getElementById('edit_org_password');
      if (!inp) return;
      const icon = toggle.querySelector('i');
      if (inp.type === 'password') { inp.type = 'text'; if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } }
      else { inp.type = 'password'; if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); } }
    });
  }
  document.getElementById('editOrgForm').onsubmit = function(e) {
 
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    fetch('api/save_organization.php', {
  method: 'POST',
  body: formData
})
.then(() => {

  closeModal('editModal');

  window.location.reload();

})
.catch(() => window.location.reload());

  };
}


renderOrg();


  window.showAddProjectModal = function() {
    const main = document.querySelector('main.org-main');
    const orgId = main ? main.dataset.orgId : null;
    if (!orgId) return alert('Organization not found');
    const modal = document.getElementById('projectModal');
    const body = document.getElementById('projectModalBody');
    if (!modal || !body) return showEditProjectModal({ id: Date.now(), name: '', description: '' }, true);

    body.innerHTML = `
      <h3 style="margin-top:0">Create New Project</h3>
      <form id="quickAddProjectForm">
        <label>Name</label>
        <input type="text" id="quickProjectName" name="name" required style="width:100%; padding:8px; margin-bottom:8px;">
        <label>Description</label>
        <textarea id="quickProjectDesc" name="description" rows="3" style="width:100%; padding:8px; margin-bottom:8px;"></textarea>
        <div style="display:flex; gap:8px;">
          <div style="flex:1">
            <label>Due Date</label>
            <input type="date" id="quickProjectDue" name="due_date" style="width:100%; padding:8px;">
          </div>
          <div style="width:140px">
            <label>Priority</label>
            <select id="quickProjectPriority" name="priority" style="width:100%; padding:8px;">
              <option value="high">High</option>
              <option value="medium" selected>Medium</option>
              <option value="low">Low</option>
            </select>
          </div>
          <div style="width:140px">
            <label>Visibility</label>
            <select id="quickProjectVisibility" name="visibility" style="width:100%; padding:8px;">
              <option value="private" selected>Private</option>
              <option value="public">Public</option>
            </select>
          </div>
        </div>
        <div style="margin-top:12px; display:flex; gap:8px; justify-content:flex-end;">
          <button type="button" id="cancelQuickAdd" class="org-btn delete">Cancel</button>
          <button type="submit" class="org-btn add">Create</button>
        </div>
      </form>
    `;


    modal.style.display = 'flex';

    document.getElementById('cancelQuickAdd').onclick = () => { modal.style.display = 'none'; };

    document.getElementById('quickAddProjectForm').onsubmit = async function(e) {
      e.preventDefault();
      const name = document.getElementById('quickProjectName').value.trim();
      if (!name) return alert('Project name is required');
      const description = document.getElementById('quickProjectDesc').value;
      const due_date = document.getElementById('quickProjectDue').value;
      const priority = document.getElementById('quickProjectPriority').value;
      const visibility = document.getElementById('quickProjectVisibility').value;

      const fd = new FormData();
      fd.append('name', name);
      fd.append('description', description);
      if (due_date) fd.append('due_date', due_date);
      fd.append('priority', priority);
      fd.append('visibility', visibility);
      fd.append('organization_id', orgId);

      try {
    
        const res = await fetch('api/save_project.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const text = await res.text();
        console.log('save_project response', res.status, res.redirected, text.slice(0, 400));
  
        const lower = text.toLowerCase();
        if (lower.includes('you are not a member') || lower.includes('missing organization context') || lower.includes("alert('") || lower.includes('alert("')) {
   
          alert('Server response: ' + text.replace(/<[^>]*>/g, '').trim().slice(0, 300));
          return;
        }
        if (!res.ok) {
   
          alert('Server error creating project: ' + (text || res.statusText));
          return;
        }
  
        modal.style.display = 'none';
        window.location.reload();
      } catch (err) {
        console.error('Network error creating project', err);
        alert('Network error creating project');
      }
    };
  };

function calcTimeSpent(created) {
  if (!created) return '0h 0min';
  const now = new Date();
  const start = new Date(created);
  let diff = Math.max(0, now - start);
  const hours = Math.floor(diff / (1000 * 60 * 60));
  diff -= hours * 1000 * 60 * 60;
  const mins = Math.floor(diff / (1000 * 60));
  return `${hours}h ${mins}min`;
}


function setupRichTextEditor(toolbarId, contentId) {
  const toolbar = document.getElementById(toolbarId);
  const content = document.getElementById(contentId);
  toolbar.querySelectorAll('button').forEach(btn => {
    btn.onclick = function() {
      const cmd = btn.getAttribute('data-cmd');
      if (cmd === 'h1') document.execCommand('formatBlock', false, 'H1');
      else if (cmd === 'h3') document.execCommand('formatBlock', false, 'H3');
      else if (cmd === 'p') document.execCommand('formatBlock', false, 'P');
      else if (cmd === 'ul') document.execCommand('insertUnorderedList');
      else if (cmd === 'ol') document.execCommand('insertOrderedList');
      else if (cmd === 'checkbox') {
        document.execCommand('insertHTML', false, '<input type="checkbox" style="margin-right:8px;">');
      }
      else if (cmd === 'bold') document.execCommand('bold');
      else if (cmd === 'italic') document.execCommand('italic');
      content.focus();
    };
  });
}