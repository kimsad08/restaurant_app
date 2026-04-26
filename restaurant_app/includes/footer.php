<script>
function openModal(id) {
  document.getElementById(id).classList.add('open');
}
function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

let pendingDeleteForm = null;
function confirmDelete(formId) {
  pendingDeleteForm = document.getElementById(formId);
  document.getElementById('confirmOverlay').classList.add('open');
}
function doDelete() {
  if (pendingDeleteForm) pendingDeleteForm.submit();
}
function cancelDelete() {
  document.getElementById('confirmOverlay').classList.remove('open');
  pendingDeleteForm = null;
}

document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
  });
});
</script>

<div class="confirm-overlay" id="confirmOverlay">
  <div class="confirm-box">
    <div class="confirm-icon">🗑️</div>
    <div class="confirm-title">ยืนยันการลบ</div>
    <div class="confirm-text">ข้อมูลที่ลบแล้วไม่สามารถกู้คืนได้ ต้องการลบหรือไม่?</div>
    <div class="confirm-btns">
      <button class="btn btn-danger" onclick="doDelete()">ลบเลย</button>
      <button class="btn" style="background:var(--surface2);color:var(--text)" onclick="cancelDelete()">ยกเลิก</button>
    </div>
  </div>
</div>

</body>
</html>