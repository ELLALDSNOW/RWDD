<?php
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: login.html'); exit;
}
require_once 'conn.php';

$user_id = intval($_SESSION['user_id']);


$stmt = mysqli_prepare($dbConn, "SELECT organization_id, name, description, priority, created_at FROM organization ORDER BY name ASC");
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$orgs = [];
if ($res) { while ($r = mysqli_fetch_assoc($res)) $orgs[] = $r; }
mysqli_stmt_close($stmt);

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Organizations • Hive</title>
  <link rel="stylesheet" href="personal.css">
  <style>
    
    .org-grid { display:grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap:18px; padding:28px; max-width:1200px; margin:30px auto; }
    .org-card { background:#fff; border-radius:12px; padding:18px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); display:flex; flex-direction:column; justify-content:space-between; cursor:pointer; transition: transform .18s ease, box-shadow .18s ease; }
    .org-card:hover{ transform:translateY(-6px); box-shadow: 0 30px 60px rgba(0,0,0,0.12); }
    .org-name{ font-weight:800; font-size:1.05rem; margin-bottom:6px; color:var(--text); }
    .org-meta{ color:rgba(0,0,0,0.6); font-size:0.95rem; }
    .page-header{ max-width:1200px; margin:18px auto; padding:0 28px; display:flex; align-items:center; justify-content:space-between; }
    .page-header h1{ margin:0; font-size:1.6rem; }
    /* modal */
    .modal-pass { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.45); z-index:1200; }
    .modal-pass .box{ background:#fff; padding:20px; border-radius:12px; width:360px; max-width:92vw; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
    .modal-pass label{ display:block; font-weight:700; margin-bottom:6px; }
    .modal-pass input{ width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:12px; }
    .btn { background:#ffd700; border:none; padding:10px 14px; border-radius:8px; font-weight:700; cursor:pointer; }
    .btn.ghost{ background:#fff; border:1px solid #ddd; }
  </style>
</head>
<body>
  <header class="header">
    <div class="left-section">
      <a href="index.html"><img src="images/Group 24.png" alt="logo" class="logo"></a>
      <span class="brand">HIVE</span>
    </div>
    <div class="right-section"><!-- empty --></div>
  </header>

  <main>
    <div class="page-header">
      <h1>Join an Organization</h1>
      <div style="color:rgba(0,0,0,0.6);">Click an org to request access</div>
    </div>

    <div class="org-grid" id="orgGrid">
      <?php foreach($orgs as $o): ?>
        <div class="org-card" data-id="<?php echo (int)$o['organization_id']; ?>">
          <div>
            <div class="org-name"><?php echo esc($o['name']); ?></div>
            <div class="org-meta"><?php echo esc($o['description'] ?: 'No description'); ?></div>
          </div>
          <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center;">
            <div style="font-size:.9rem; color:rgba(0,0,0,0.5);">Priority: <?php echo esc($o['priority'] ?: 'Medium'); ?></div>
            <div style="font-size:.85rem; color:rgba(0,0,0,0.5);">ID: <?php echo (int)$o['organization_id']; ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <div class="modal-pass" id="passModal">
      <div class="box">
      <h3 id="modalOrgName" style="margin-top:0">Enter organization password</h3>
      <form id="passForm">
        <input type="hidden" name="org_id" id="modalOrgId">
        <label for="org_password">Password</label>
        <div style="display:flex; align-items:center; gap:8px;">
          <input id="org_password" name="password" type="password" autocomplete="off" required style="flex:1;">
          <button type="button" id="join_org_password_toggle" class="pwd-toggle" aria-label="Show password"><i class="fa fa-eye"></i></button>
        </div>
        <div style="display:flex; gap:10px; justify-content:flex-end">
          <button type="button" class="btn ghost" id="cancelPass">Cancel</button>
          <button class="btn" type="submit">Enter</button>
        </div>
        <div id="passMsg" style="margin-top:8px;color:#c33;display:none"></div>
      </form>
    </div>
  </div>

  <script>
    const orgGrid = document.getElementById('orgGrid');
    const modal = document.getElementById('passModal');
    const modalName = document.getElementById('modalOrgName');
    const modalOrgId = document.getElementById('modalOrgId');
    const passForm = document.getElementById('passForm');
    const passMsg = document.getElementById('passMsg');
    const cancelBtn = document.getElementById('cancelPass');

    orgGrid.addEventListener('click', (e)=>{
      const card = e.target.closest('.org-card');
      if (!card) return;
      const id = card.dataset.id;
      const name = card.querySelector('.org-name').textContent.trim();
      modalOrgId.value = id;
      modalName.textContent = 'Enter password for "' + name + '"';
      passMsg.style.display = 'none';
      document.getElementById('org_password').value = '';
      modal.style.display = 'flex';
    });

    cancelBtn.onclick = ()=>{ modal.style.display='none'; };

    passForm.onsubmit = async function(e){
      e.preventDefault();
      passMsg.style.display = 'none';
      const fd = new FormData(passForm);
      try {
        const res = await fetch('api/check_org_password.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        if (!res.ok) throw new Error('Network');
        const json = await res.json();
        if (json.ok) {
          
          window.location.href = 'organization.php?org_id=' + encodeURIComponent(json.org_id);
        } else {
          passMsg.textContent = (json.error === 'incorrect') ? 'Incorrect password' : (json.error || 'Error');
          passMsg.style.display = 'block';
        }
      } catch(err) {
        passMsg.textContent = 'Request failed'; passMsg.style.display = 'block';
      }
    };

    
    const joinToggle = document.getElementById('join_org_password_toggle');
    if (joinToggle) {
      joinToggle.addEventListener('click', ()=>{
        const p = document.getElementById('org_password');
        if (!p) return;
        const icon = joinToggle.querySelector('i');
        if (p.type === 'password') { p.type='text'; if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); } }
        else { p.type='password'; if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); } }
      });
    }
  </script>
</body>
</html>
