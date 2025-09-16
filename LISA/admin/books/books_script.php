<script>
  if (window.jQuery) {
    // Build the DataTable for this render
    const dt = jQuery('#booksTable').DataTable({
      dom: 't<"dt-footer"ip>', // keep toolbar UI custom
      pageLength: 10,
      lengthChange: false,
      order: [],
      columnDefs: [{
        orderable: false,
        targets: [5]
      }]
    });

    // Wire up toolbar controls (fresh DOM each render)
    const q = document.getElementById('bookSearch');
    const cat = document.getElementById('bookCategory');
    const btn = document.getElementById('btnSearch');

    function applyFilters() {
      // global text search
      dt.search(q.value || '');

      // exact match on Category column (index 3)
      const val = cat.value || '';
      dt.column(3).search(val, true, false);

      dt.draw();
    }

    q.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') applyFilters();
    });
    btn.addEventListener('click', applyFilters);
    cat.addEventListener('change', applyFilters);

    document.getElementById('btnAddBook').addEventListener('click', () => {
      alert('Open "Add Book" form/modal here.');
    });
  }
</script>