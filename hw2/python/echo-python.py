#!/usr/bin/env python3
import os
import sys
import json
from urllib.parse import parse_qs
from datetime import datetime

# ---------------- REQUEST METADATA ----------------
method = os.environ.get("REQUEST_METHOD", "UNKNOWN")
query_string = os.environ.get("QUERY_STRING", "")
query = parse_qs(query_string)

content_length = int(os.environ.get("CONTENT_LENGTH", 0))
content_type = os.environ.get("CONTENT_TYPE", "")

raw_body = sys.stdin.read(content_length) if content_length > 0 else ""

# ---------------- BODY PARSING ----------------
body = None
if raw_body:
    if "application/json" in content_type:
        try:
            body = json.loads(raw_body)
        except:
            body = raw_body
    else:
        body = parse_qs(raw_body)

# ---------------- RESPONSE ----------------
response = {
    "method": method,
    "hostname": os.environ.get("SERVER_NAME", "unknown"),
    "time": datetime.now().strftime("%a %b %d %H:%M:%S %Y"),
    "ip": os.environ.get(
        "HTTP_X_FORWARDED_FOR",
        os.environ.get("REMOTE_ADDR", "unknown")
    ).split(",")[0],
    "user_agent": os.environ.get("HTTP_USER_AGENT", "unknown"),
    "query": query,
    "body": body
}

print("Content-Type: application/json\n")
print(json.dumps(response, indent=2))
