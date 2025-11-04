<script>
  (function() {
    const MAX_BORROW = 3; // adjust if your policy changes
    const $table = $('#studentsTable');

    // Small debounce for search box
    const debounce = (fn, ms = 250) => {
      let t;
      return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(null, args), ms);
      };
    };

    // Init DataTable
    const dt = $table.DataTable({
      processing: true,
      ajax: {
        url: 'students_data.php',
        dataSrc: 'data'
      },
      order: [
        [2, 'asc']
      ],
      scrollX: true, // ← enable horizontal scroll
      scrollCollapse: true, // ← collapse when not needed
      autoWidth: false, // ← let us control widths + avoid odd stretching
      columns: [{
          data: 'rfid'
        }, // 0 Hidden
        {
          data: 'student_id'
        }, // 1
        {
          data: 'name'
        }, // 2
        {
          data: 'email'
        }, // 3
        {
          data: 'course'
        }, // 4
        { // 5 Books Borrowed (pretty)
          data: 'borrowed',
          render: function(val, type, row) {
            const used = Number(val) || 0;
            const text = `${used}/${MAX_BORROW}`;
            if (type === 'display') {
              const cls = used >= MAX_BORROW ? 'cap-full' : (used > 0 ? 'cap-some' : 'cap-zero');
              return `<span class="${cls}" title="${used} of ${MAX_BORROW}">${text}</span>`;
            }
            return used;
          }
        },
        { // 6 Actions
          data: null,
          orderable: false,
          searchable: false,
          width: 140,
          render: function(data, type, row) {
            const sid = row.student_id ? String(row.student_id).replace(/"/g, '&quot;') : '';
            return `
              <div class="row-actions">
                <button class="table-btn del" data-sid="${sid}" title="Delete">Delete</button>
              </div>`;
          }
        }
      ],
      columnDefs: [{
          targets: 0,
          visible: false,
          searchable: false
        }, // hide RFID
      ],
      // Responsive/nowrap is already in your table classes
      drawCallback: function() {
        // (Optional) hook for post-draw tweaks
      }
    });

    // Make sure widths compute correctly at start and on resize
    dt.columns.adjust();
    $(window).on('resize', debounce(() => dt.columns.adjust(), 150));

    // Expose the table so modal_script can reload after create
    window.studentsTable = dt;

    // Connect top search box to DataTable global search
    const $search = $('#studentSearch');
    $search.on('input', debounce(function() {
      dt.search(this.value || '').draw();
    }, 200));

    // Open the registration modal
    $('#btnAddStudent').on('click', function() {
      window.openStudentRegModal && window.openStudentRegModal(this);
    });

    // Delegated handlers for Actions (you can replace with your own modals/routes)
    $table.on('click', '.icon-btn.edit', function() {
      const sid = this.getAttribute('data-sid');
      // TODO: open edit modal / navigate to edit page
      alert('Edit student: ' + sid);
    });
    $table.on('click', '.table-btn.del', function() {
      const sid = this.getAttribute('data-sid');
      if (!sid) {
        alert('Missing student_id');
        return;
      }
      if (!confirm(`Delete student ${sid}? This cannot be undone.`)) return;
      console.log('Deleting student', sid);

      fetch('delete_student.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            student_id: sid
          })
        })
        .then(r => r.json())
        .then(res => {
          if (!res || !res.ok) {
            alert(res?.error || 'Delete failed.');
            return;
          }
          // reload table but stay on the current page
          dt.ajax.reload(null, false);
          // optional: toast if you use toastr
          // toastr.success(`Deleted ${sid}`);
        })
        .catch(() => alert('Server error while deleting.'));
    });
  })();
</script>