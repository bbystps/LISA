<script>
  (function() {
    const $table = $('#booksTable');

    const dt = $table.DataTable({
      processing: true,
      ajax: {
        url: 'transactions_data.php',
        dataSrc: 'data'
      },
      order: [
        [9, 'asc'],
        [5, 'desc']
      ],
      scrollX: true,
      scrollCollapse: true,
      autoWidth: false,
      columns: [{
          data: 'id'
        }, // 0 hidden
        {
          data: 'student_id'
        }, // 1 hidden
        {
          data: 'book_id'
        }, // 2 hidden
        {
          data: 'Name',
          defaultContent: '-'
        }, // 3
        {
          data: 'Title',
          defaultContent: '-'
        }, // 4
        {
          data: 'Author',
          defaultContent: '-'
        }, // 5
        {
          data: 'Borrow Date',
          defaultContent: '-'
        }, // 6
        {
          data: 'Return Date',
          defaultContent: '-'
        }, // 7
        {
          data: 'Status',
          defaultContent: '-'
        }, // 8
        {
          data: 'Flag',
          defaultContent: '-'
        }, // 9 hidden
        {
          data: 'Location',
          defaultContent: '-'
        }, // 10
        {
          data: null,
          orderable: false,
          searchable: false,
          width: 220,
          render: function(data, type, row) {
            const sid = String(row.student_id ?? '').replace(/"/g, '&quot;');
            const bid = String(row.book_id ?? '').replace(/"/g, '&quot;');
            const loc = String(row.Location ?? '').replace(/"/g, '&quot;');
            const id = String(row.id ?? '').replace(/"/g, '&quot;');

            const flag = String(row.Flag ?? '').trim().toUpperCase();
            const status = String(row.Status ?? '').trim().toLowerCase();

            let label = 'Unavailable',
              action = '',
              disabled = true;

            if (flag === 'ACTIVE') {
              if (status === 'to deliver') {
                label = 'Deliver';
                action = 'deliver';
                disabled = false;
              } else if (status === 'delivered') {
                label = 'Acknowledge';
                action = 'ack';
                disabled = false;
              }
            }

            const disAttr = disabled ? 'disabled' : '';
            const actAttr = action ? `data-action="${action}"` : '';

            return `
              <div class="row-actions">
                <button class="table-btn primary"
                        data-id="${id}"
                        data-student_id="${sid}"
                        data-book_id="${bid}"
                        data-location="${loc}"
                        ${actAttr}
                        title="${label}" ${disAttr}>${label}</button>
              </div>`;
          }
        }
      ],
      columnDefs: [{
          targets: [0, 1, 2, 9],
          visible: false,
          searchable: false
        },
        {
          targets: [6, 7],
          type: 'date'
        }
      ]
    });

    window.transactionsTable = dt;

    // --- Primary action handler (Deliver / Acknowledge) ---
    $table.on('click', '.table-btn.primary', function() {
      if (this.disabled) return;

      const id = this.getAttribute('data-id');
      const studentId = this.getAttribute('data-student_id');
      const bookId = this.getAttribute('data-book_id');
      const location = this.getAttribute('data-location');
      const action = this.getAttribute('data-action'); // 'deliver' | 'ack'
      if (!action) return;

      if (action === "deliver") {
        $.ajax({
          type: 'POST',
          url: 'status_update.php',
          data: {
            id,
            action: 'deliver'
          },
          cache: false,
          success: function() {
            // Notify robot
            const payload = JSON.stringify({
              transaction_id: id,
              task: "deliver",
              student_id: studentId,
              book_id: bookId,
              location
            });
            var message = new Messaging.Message(payload);
            message.destinationName = "LISA/RobotTask";
            message.qos = 0;
            client.send(message);

            // Refresh table
            window.transactionsTable.ajax.reload(null, false);
          },
          error: function(xhr) {
            console.error(xhr.responseText);
            renderError('Network error. Please try again.');
          }
        });
      } else if (action === "ack") {
        $.ajax({
          type: 'POST',
          url: 'status_update.php',
          data: {
            id,
            action: 'ack'
          },
          cache: false,
          success: function() {
            // Optional: Publish an MQTT acknowledgement event
            const payload = JSON.stringify({
              transaction_id: id,
              task: "ack",
              student_id: studentId,
              book_id: bookId,
              location
            });
            var message = new Messaging.Message(payload);
            message.destinationName = "LISA/RobotTask";
            message.qos = 0;
            client.send(message);

            // Refresh table (will disappear since flag becomes DONE)
            window.transactionsTable.ajax.reload(null, false);
          },
          error: function(xhr) {
            console.error(xhr.responseText);
            renderError('Network error. Please try again.');
          }
        });
      }
    });
  })();
</script>