import uuid
import json
import os

QUEUE_FILE = "sms_queue.json"

def add_to_queue(to, message):
    # Generate a unique ID for the message
    message_id = str(uuid.uuid4())
    
    # Load the existing queue if any
    try:
        if os.path.exists(QUEUE_FILE):
            with open(QUEUE_FILE, "r") as file:
                queue = json.load(file)
        else:
            queue = []
    except json.JSONDecodeError as e:
        print("Error loading the queue file:", e)
        queue = []

    # Add the new message with a unique ID
    queue.append({"id": message_id, "to": to, "message": message})
    
    # Print queue to ensure it's being added
    print("Current queue:", queue)

    # Save the updated queue back to the file
    try:
        with open(QUEUE_FILE, "w") as file:
            json.dump(queue, file, indent=4)
        print(f"SMS added to queue with ID: {message_id}")
    except Exception as e:
        print("Error saving the queue:", e)

# Example usage
add_to_queue("+639057376078", "Hello! This is queued 1.")
