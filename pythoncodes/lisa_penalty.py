#!/usr/bin/env python3
import pymysql
import datetime as dt
import pytz
import sys

DB_CFG = dict(
    host="localhost",
    user="root",
    password="",
    database="lisa",
    autocommit=True,
)

TZ = pytz.timezone("Asia/Manila")

# Which statuses should incur penalties while still PENDING
PENALTY_STATUSES = (
    "Borrowed", "Returning", "To Fetch", "Delivering", "Fetching"
    # Add more if needed: "To Prepare", etc.
)

def get_db():
    conn = pymysql.connect(**DB_CFG)
    cur = conn.cursor()
    return conn, cur

def ensure_unique_key(cur):
    """Make ON DUPLICATE KEY work on (student_id, book_id, transaction_id)."""
    try:
        cur.execute("ALTER TABLE penalties ADD UNIQUE KEY uniq_pen (student_id, book_id, transaction_id)")
    except Exception:
        # Likely already exists; ignore
        pass

def read_settings(cur):
    cur.execute("SELECT daily_rate, grace_days FROM penalty_settings WHERE id=1")
    row = cur.fetchone()
    if not row:
        # Fallback defaults if row missing
        return 5.00, 1
    return float(row[0]), int(row[1])

def fetch_overdue_active(cur, grace_days):
    """
    Get ACTIVE overdue transactions beyond grace.
    days_late is computed in SQL for the current day.
    """
    sql = f"""
        SELECT
            t.id AS transaction_id,
            t.student_id,
            t.book_id,
            t.return_date AS due_date,
            GREATEST(DATEDIFF(CURDATE(), t.return_date) - %s, 0) AS days_late_calc
        FROM transactions t
        WHERE t.flag = 'PENDING'
          AND t.status IN ({",".join(["%s"]*len(PENALTY_STATUSES))})
          AND t.return_date < DATE_SUB(CURDATE(), INTERVAL %s DAY)
    """
    params = [grace_days, *PENALTY_STATUSES, grace_days]
    cur.execute(sql, params)
    return cur.fetchall()

def upsert_penalty(cur, student_id, book_id, tx_id, due_date, days_late, amount):
    """
    Insert new 'Unpaid' penalty or update existing one for the same (student,book,tx).
    """
    sql = """
        INSERT INTO penalties
            (student_id, book_id, transaction_id, due_date, days_late, amount, status)
        VALUES
            (%s, %s, %s, %s, %s, %s, 'Unpaid')
        ON DUPLICATE KEY UPDATE
            days_late = VALUES(days_late),
            amount    = VALUES(amount),
            status    = 'Unpaid'
    """
    cur.execute(sql, (student_id, book_id, tx_id, due_date, days_late, amount))

def run_once():
    conn, cur = get_db()
    try:
        ensure_unique_key(cur)
        daily_rate, grace_days = read_settings(cur)

        rows = fetch_overdue_active(cur, grace_days)
        if not rows:
            print("[penalty-cron] No overdue PENDING transactions beyond grace.")
            return

        count = 0
        for tx_id, student_id, book_id, due_date, days_late in rows:
            days_late = max(int(days_late or 0), 0)
            if days_late <= 0:
                # Not billable today (within grace or not yet overdue)
                continue
            amount = round(days_late * daily_rate, 2)
            upsert_penalty(cur, student_id, book_id, tx_id, due_date, days_late, amount)
            count += 1

        print(f"[penalty-cron] Upserted/updated {count} penalty rows (rate={daily_rate}, grace={grace_days}).")

    except Exception as e:
        print(f"[penalty-cron] ERROR: {e}", file=sys.stderr)
        raise
    finally:
        try:
            cur.close()
        except Exception:
            pass
        try:
            conn.close()
        except Exception:
            pass

if __name__ == "__main__":
    # Optional: only run at local 08:00 if you insist on running as a daemon.
    # Best practice is to use cron (see below). Keeping here for manual/one-off runs.
    now = dt.datetime.now(TZ)
    print(f"[penalty-cron] Running at {now.strftime('%Y-%m-%d %H:%M:%S %Z')}")
    run_once()
