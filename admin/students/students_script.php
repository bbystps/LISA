<?php // students_script.php 
?>
<script>
  (function() {
    if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) {
      console.warn('DataTables not found. Load jQuery and DataTables before students_script.php');
      return;
    }

    if (jQuery.fn.dataTable.isDataTable('#studentsTable')) {
      jQuery('#studentsTable').DataTable().destroy(true);
    }

    const dt = jQuery('#studentsTable').DataTable({
      dom: 't<"dt-footer"ip>',
      pageLength: 10,
      lengthChange: false,
      order: [],
      columnDefs: [
        // Hide RFID column, keep it out of search
        {
          targets: [0],
          visible: false,
          searchable: false
        },
        // Actions column not sortable (remember hidden column still counts in index)
        {
          targets: [6],
          orderable: false
        }
      ]
    });

    // Global search
    const q = document.getElementById('studentSearch');
    const applySearch = () => dt.search(q?.value || '').draw();

    // lightweight debounce
    let t;
    q?.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(applySearch, 180);
    });
    q?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') applySearch();
    });

    // Add Student
    document.getElementById('btnAddStudent')?.addEventListener('click', () => {
      // TODO: open modal or redirect
      // openModal('studentCreateModal') OR location.href = 'student_create.php';
      alert('Open "Add Student" form/modal here.');
    });

    // Row actions (Edit/Delete)
    document.getElementById('studentsTable')?.addEventListener('click', (e) => {
      const btn = e.target.closest('button.icon-btn');
      if (!btn) return;

      const rowEl = btn.closest('tr');
      const row = dt.row(rowEl);
      const data = row.data(); // [RFID, StudentID, Name, Email, Course, Borrowed, ActionsHTML]

      const rfid = (data?.[0] || '').toString().replace(/<[^>]+>/g, '');
      const studentId = (data?.[1] || '').toString().replace(/<[^>]+>/g, '');
      const name = (data?.[2] || '').toString().replace(/<[^>]+>/g, '');

      if (btn.classList.contains('edit')) {
        // TODO: modal or navigate with ID
        // location.href = 'student_edit.php?id=' + encodeURIComponent(studentId);
        alert(`Edit student: ${name} (${studentId})\nRFID: ${rfid}`);
        return;
      }

      if (btn.classList.contains('del')) {
        if (!confirm(`Delete ${name} (${studentId})? This cannot be undone.`)) return;

        // TODO: call delete API, then on success:
        // fetch('student_delete.php', { method:'POST', body:new URLSearchParams({ id: studentId })})
        //   .then(r=>r.json()).then(j => { if (j.ok) row.remove().draw(); else alert(j.msg || 'Delete failed'); });

        // Demo only:
        row.remove().draw();
        return;
      }
    });
  })();
</script>