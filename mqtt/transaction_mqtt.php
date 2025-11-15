<script>
    var i = 0;
    var client = new Messaging.Client("13.214.212.87", 9001, "myclientid_" + parseInt(Math.random() * 100, 10));
    //a = new AudioContext(); // browsers limit the number of concurrent audio contexts,

    client.onConnectionLost = function(responseObject) {
        toastr.error('Trying to reconnect...', 'Server not responding.', {
            closeButton: true,
            timeOut: 3000,
            progressBar: true,
            allowHtml: true
        });
        MQTTreconnect();
    };

    function MQTTreconnect() {
        if (client.connected) {
            return;
        }
        //console.log("ATTEMPTING TO RECONNECT");
        // Set a timeout before attempting to reconnect
        setTimeout(function() {
            // Try to reconnect
            client.connect(options);
        }, 5000); // You can adjust the timeout duration as needed
    }

    //Connect Options
    var options = {
        timeout: 3,
        keepAliveInterval: 60,
        userName: '*****',
        password: '*****',
        onSuccess: function() {
            client.subscribe('LISA/TransactionUpdated', {
                qos: 0
            });
            client.subscribe('LISA/RobotMobility', {
                qos: 0
            });
            client.subscribe('LISA/User', {
                qos: 0
            });
            toastr.success('', 'Server OK!');

            var message = new Messaging.Message('New Message');
            message.destinationName = 'newTopicTest';
            message.qos = 0;
            client.send(message);
        },

        onFailure: function(message) {
            //beeps(10,1000,1000);
            toastr.error('Trying to reconnect...', 'Server not responding.', {
                closeButton: true,
                timeOut: 3000,
                progressBar: true,
                allowHtml: true
            });
            MQTTreconnect();
        }

    };

    var publish = function(payload, topic, qos) {
        var message = new Messaging.Message(payload);
        message.destinationName = topic;
        message.qos = qos;
        client.send(message);
    }

    client.onMessageArrived = function(message) {
        var x = message.payloadString;
        console.log(x);
        //a = new AudioContext(); // browsers limit the number of concurrent audio contexts,
        if (message.destinationName == "LISA/TransactionUpdated") {
            // toastr.success('Transaction', x);

            const x = message.payloadString;
            let data;
            try {
                data = JSON.parse(x);
            } catch (e) {
                console.warn("Non-JSON or malformed payload:", x);
                return;
            }

            // Only toast/reload if something actually changed
            const updated = Number(data?.updated_rows || 0);
            if (updated <= 0) return;

            const loc = String(data?.location || "").trim();
            const studentId = data?.student_id;
            const txnId = data?.transaction_id;
            const statusRaw = data?.status;

            // Normalize status (Delivered/Returned)
            const status = typeof statusRaw === "string" ?
                statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1).toLowerCase() :
                statusRaw;

            let msg = null;

            if ((loc === "TableA" || loc === "TableB") && studentId) {
                msg = `Student with ID ${studentId} has sat at ${loc}.`;
            } else if (loc === "Entrance" && studentId) {
                msg = `Student with ID ${studentId} has entered the library.`;
            } else if (loc === "Robot" && txnId && status) {
                msg = `Transaction ID ${txnId} has been successfully ${status}.`;
            }

            if (msg) {
                toastr.success(msg, "Transaction");
            }

            // ✅ reload only the table data (stay on same page)
            if (window.transactionsTable) {
                window.transactionsTable.ajax.reload(null, false);
            }

        } else if (message.destinationName == "LISA/User") {

            // ✅ reload only the table data (stay on same page)
            if (window.transactionsTable) {
                window.transactionsTable.ajax.reload(null, false);
            }

        } else if (message.destinationName == "LISA/RobotMobility") {
            if (x == "Enabled") {
                // Disable Go Button
                document.getElementById("btnGoDeliver").disabled = true;
                toastr.info("Robot Mobility Enabled — GO button disabled.");
            } else if (x == "Disabled") {
                // Enable Go Button
                document.getElementById("btnGoDeliver").disabled = false;
                toastr.info("Robot Mobility Disabled — GO button enabled.");
            }
        }
    }
</script>