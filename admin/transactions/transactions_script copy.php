<script>
  (function() {
    const $table = $('#booksTable');
    // Config: total items allowed (deliver + fetch)
    const MAX_SELECTION_TOTAL = 3;

    // Two selection maps
    const selectedDeliver = new Map(); // id -> {id, student_id, book_id, location}
    const selectedFetch = new Map(); // id -> {id, student_id, book_id, location}

    const $btnGo = $('#btnGoDeliver'); // reuse same GO button
    const $selCount = $('#selCount');

    function totalSelected() {
      return selectedDeliver.size + selectedFetch.size;
    }

    function updateGoButton() {
      const n = totalSelected();
      $selCount.text(n);
      $btnGo.prop('disabled', n === 0);
    }

    const dt = $table.DataTable({
      processing: true,
      ajax: {
        url: 'transactions_data.php',
        dataSrc: 'data'
      },
      order: [
        [0, 'asc']
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
                action = 'select_deliver';
                const isSelected = selectedDeliver.has(id);
                label = isSelected ? 'Selected (Deliver)' : 'Select (Deliver)';
                disabled = false;
              } else if (status === 'to fetch') {
                action = 'select_fetch';
                const isSelected = selectedFetch.has(id);
                label = isSelected ? 'Selected (Fetch)' : 'Select (Fetch)';
                disabled = false;
              } else if (status === 'delivered') {
                label = 'Acknowledge';
                action = 'ack';
                disabled = false;
              } else if (status === 'returned') {
                label = 'Acknowledge';
                action = 'ack_returned';
                disabled = false;
              }
            }

            const disAttr = disabled ? 'disabled' : '';
            const actAttr = action ? `data-action="${action}"` : '';

            const selectedClass =
              (action === 'select_deliver' && selectedDeliver.has(id)) ||
              (action === 'select_fetch' && selectedFetch.has(id)) ?
              ' selected' :
              '';

            return `
              <div class="row-actions">
                <button class="table-btn primary${selectedClass}"
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
      }]
    });

    window.transactionsTable = dt;

    // Redraw keeps the "Selected" look based on selectedDeliver map
    dt.on('draw', updateGoButton);

    // --- Row button handler ---
    $table.on('click', '.table-btn.primary', function() {
      if (this.disabled) return;

      const id = this.getAttribute('data-id');
      const studentId = this.getAttribute('data-student_id');
      const bookId = this.getAttribute('data-book_id');
      const location = this.getAttribute('data-location');
      const action = this.getAttribute('data-action');

      if (!action) return;
      // 1) SELECTION TOGGLE FOR DELIVERY / FETCH
      if (action === 'select_deliver' || action === 'select_fetch') {
        const isDeliver = (action === 'select_deliver');
        const bag = isDeliver ? selectedDeliver : selectedFetch;

        if (bag.has(id)) {
          // Unselect from its bag
          bag.delete(id);
        } else {
          // Enforce a shared total cap
          if (totalSelected() >= MAX_SELECTION_TOTAL) {
            toastr.warning(`You can only select up to ${MAX_SELECTION_TOTAL} items total (Deliver + Fetch).`);
            return;
          }
          bag.set(id, {
            id,
            student_id: studentId,
            book_id: bookId,
            location
          });
        }

        // Update label/appearance
        const nowSelected = bag.has(id);
        this.textContent = nowSelected ?
          (isDeliver ? 'Selected (Deliver)' : 'Selected (Fetch)') :
          (isDeliver ? 'Select (Deliver)' : 'Select (Fetch)');

        this.classList.toggle('selected', nowSelected);
        updateGoButton();
        return;
      }

      // 2) EXISTING SINGLE-ROW ACTIONS
      if (action === 'ack' || action === 'fetch' || action === 'ack_returned') {
        $.ajax({
          type: 'POST',
          url: 'status_update.php',
          data: {
            id,
            action
          },
          cache: false,
          success: function() {
            // Mirror your current MQTT behavior
            const payload = JSON.stringify({
              transaction_id: id,
              task: action,
              student_id: studentId,
              book_id: bookId,
              location
            });
            var message = new Messaging.Message(payload);
            message.destinationName = "LISA/RobotTask";
            message.qos = 0;
            client.send(message);

            window.transactionsTable.ajax.reload(null, false);
          },
          error: function(xhr) {
            console.error(xhr.responseText);
            renderError('Network error. Please try again.');
          }
        });
      }
    });

    // --- GO button: batch both Deliver and Fetch selections ---
    $btnGo.on('click', async function() {
      if (totalSelected() === 0) return;

      $btnGo.prop('disabled', true);

      // Snapshot current selections (so redraws won't affect while we process)
      const deliverTasks = Array.from(selectedDeliver.values());
      const fetchTasks = Array.from(selectedFetch.values());

      const succeeded = []; // items that updated DB successfully

      // Helper to update one id with action, then collect for MQTT
      async function updateOne(t, actionName) {
        await new Promise((resolve) => {
          $.ajax({
            type: 'POST',
            url: 'status_update.php',
            data: {
              id: t.id,
              action: actionName
            },
            cache: false,
            success: function() {
              succeeded.push({
                transaction_id: t.id,
                task: actionName, // "deliver" or "fetch"
                student_id: t.student_id,
                book_id: t.book_id,
                location: t.location
              });
              resolve();
            },
            error: function(xhr) {
              console.error(xhr.responseText);
              toastr.error(`Failed to mark ID ${t.id} as ${actionName}. Skipped.`);
              resolve();
            }
          });
        });
      }

      // 1) DB updates (sequential, preserves order)
      for (const t of deliverTasks) {
        /* eslint-disable no-await-in-loop */
        await updateOne(t, 'deliver');
      }
      for (const t of fetchTasks) {
        await updateOne(t, 'fetch');
      } /* eslint-enable no-await-in-loop */

      // 2) One MQTT publish with an array of mixed tasks
      if (succeeded.length > 0) {
        try {
          const payload = JSON.stringify(succeeded);
          var message = new Messaging.Message(payload);
          message.destinationName = "LISA/RobotTaskBatch"; // array payload topic
          message.qos = 0;
          client.send(message);

          toastr.success(`Sent ${succeeded.length} task(s) to robot.`);
        } catch (e) {
          console.error(e);
          toastr.error('Failed to publish batch MQTT payload.');
        }
      } else {
        toastr.warning('No items were updated; nothing to send.');
      }

      // 3) Clear & refresh
      selectedDeliver.clear();
      selectedFetch.clear();
      updateGoButton();
      window.transactionsTable.ajax.reload(null, false);
    });

  })();
</script>