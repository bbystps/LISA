import pymysql
import paho.mqtt.client as mqtt
import json
import datetime
import pytz
import time

def get_db_connection():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='',
        database='lisa',
        autocommit=True   # Important!
    )

conn = get_db_connection()
cursor = conn.cursor()

def reconnect_db():
    global conn, cursor
    try:
        conn.ping(reconnect=True)
        cursor = conn.cursor()
    except Exception as e:
        print(f"[DB] Reconnecting to database... ({e})")
        conn = get_db_connection()
        cursor = conn.cursor()

def on_connect(client, userdata, flags, rc):
    print(f"Connected with result code {rc}")
    client.subscribe("LISA/Transaction")

def on_disconnect(client, userdata, rc):
    print(f"Disconnected from MQTT broker with result code {rc}. Attempting to reconnect...")
    while True:
        try:
            client.reconnect()
            print("Reconnected successfully.")
            break
        except Exception as e:
            print(f"Reconnection failed: {e}. Retrying in 5 seconds...")
            time.sleep(5)

def on_message(client, userdata, message):
    # print(f"Message received on topic: {message.topic}")
    msg_main = str(message.payload.decode("utf-8"))
    
    if message.topic.startswith("LISA/Transaction"):
        process_transaction(client, message.topic, msg_main)

def process_transaction(client, topic, msg_main):
    try:
        json_msg_main = json.loads(msg_main)
        Location = str(json_msg_main.get("Location", "")).strip()
        loc_norm = Location.lower()

        # ---------------- Robot flow (no RFID required) ----------------
        if loc_norm == "robot":
            # Expecting: {"Location":"Robot","id":"1","status":"Delivered" or "Returned"}
            try:
                txn_id = int(json_msg_main.get("id"))
            except (TypeError, ValueError):
                print("[WARN] Robot payload missing/invalid 'id'. Skipping.")
                return

            new_status = str(json_msg_main.get("status", "")).strip()
            if not new_status:
                print("[WARN] Robot payload missing 'status'. Skipping.")
                return

            rows = update_transaction_status_by_id(txn_id, new_status)
            print(f"[INFO] Robot status update done. txn_id={txn_id}, status='{new_status}', rows={rows}")
            client.publish(
                "LISA/TransactionUpdated",
                json.dumps({"transaction_id": txn_id, "updated_rows": rows, "location": "Robot", "status": new_status}),
                qos=0, retain=False
            )
            return  # Robot handled; stop here

        # ---------------- Other flows require RFID ----------------
        RFID = json_msg_main["RFID"]
        student_id = find_student_by_rfid(RFID)
        print(f"[RFID] {RFID} -> student_id={student_id}")

        if not student_id:
            print("[WARN] No student found for this RFID. Skipping update.")
            return

        # Entrance flow
        if loc_norm == "entrance":
            rows = update_transactions_for_entrance(student_id)
            print(f"[INFO] Entrance status update done. Rows affected: {rows}")
            client.publish(
                "LISA/TransactionUpdated",
                json.dumps({"student_id": student_id, "updated_rows": rows, "location": "Entrance"}),
                qos=0, retain=False
            )

        # TableA/TableB flow
        elif loc_norm in ("tablea", "tableb"):
            table_name = "TableA" if loc_norm == "tablea" else "TableB"
            rows = update_transactions_for_table(student_id, table_name)
            print(f"[INFO] Table status update done. Rows affected: {rows} @ {table_name}")
            client.publish(
                "LISA/TransactionUpdated",
                json.dumps({"student_id": student_id, "updated_rows": rows, "location": table_name}),
                qos=0, retain=False
            )

        else:
            print(f"[INFO] Location '{Location}' does not trigger updates. No action taken.")

    except json.JSONDecodeError as e:
        print(f"Failed to decode JSON: {e}")
    except KeyError as e:
        print(f"Missing key in JSON data: {e}")
    except TypeError as e:
        print(f"Unexpected data type: {e}")
    except Exception as e:
        print(f"An unexpected error occurred: {e}")

def update_transaction_status_by_id(transaction_id, new_status):
    """
    Update a single transaction by its primary key ID.
    Returns number of rows updated (0 or 1).
    """
    try:
        reconnect_db()
        sql = """
            UPDATE transactions
            SET status = %s
            WHERE id = %s
              AND flag = 'ACTIVE'
        """
        cursor.execute(sql, (new_status, transaction_id))
        print(f"[ROBOT UPDATE] id={transaction_id}, status='{new_status}': rows_updated={cursor.rowcount}")
        return cursor.rowcount
    except Exception as e:
        print(f"[DB] Robot status update failed: {e}")
        return 0

def find_student_by_rfid(rfid_key):
    """
    Returns student_id if found, else None.
    """
    try:
        reconnect_db()
        sql = "SELECT student_id FROM students WHERE rfid_key = %s LIMIT 1"
        cursor.execute(sql, (rfid_key))
        row = cursor.fetchone()
        return row[0] if row else None
    except Exception as e:
        print(f"[DB] Error during RFID lookup: {e}")
        return None

def update_transactions_for_entrance(student_id):
    try:
        reconnect_db()
        sql = """
            UPDATE transactions
            SET status = CASE
                WHEN status = 'Reserved'  THEN 'To Prepare'
                WHEN status = 'Returning' THEN 'To Collect'
                ELSE status
            END
            WHERE student_id = %s
              AND flag = 'ACTIVE'
              AND status IN ('Reserved','Returning')
        """
        cursor.execute(sql, (student_id,))
        print(f"[ENTRANCE UPDATE] student_id={student_id}: rows_updated={cursor.rowcount}")
        return cursor.rowcount
    except Exception as e:
        print(f"[DB] Entrance status update failed: {e}")
        return 0

def update_transactions_for_table(student_id, table_name):
    """
    For ACTIVE rows of this student:
      - To Prepare -> To Deliver
      - Returning -> To Fetch
    Also sets location = table_name (e.g., 'TableA' or 'TableB').
    Returns number of rows updated.
    """
    try:
        reconnect_db()
        sql = """
            UPDATE transactions
            SET 
                status = CASE
                    WHEN status = 'To Prepare' THEN 'To Deliver'
                    WHEN status = 'To Collect' THEN 'To Fetch'
                    WHEN status = 'Returning' THEN 'To Fetch'
                    ELSE status
                END,
                location = %s
            WHERE student_id = %s
              AND flag = 'ACTIVE'
              AND status IN ('To Prepare','To Collect','Returning')
        """
        cursor.execute(sql, (table_name, student_id))
        print(f"[TABLE UPDATE] student_id={student_id}, table={table_name}: rows_updated={cursor.rowcount}")
        return cursor.rowcount
    except Exception as e:
        print(f"[DB] Table status update failed: {e}")
        return 0

client = mqtt.Client()
client.on_connect = on_connect
client.on_disconnect = on_disconnect
client.on_message = on_message

# username = "*****"
# password = "*****"
# client.username_pw_set(username, password)

try:
    client.connect("47.129.226.194", 1883, keepalive=60)
except Exception as e:
    print(f"Failed to connect to MQTT broker: {e}")
    exit(1)

client.loop_forever()
