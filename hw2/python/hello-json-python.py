#!/usr/bin/env python3
import os
import json
from datetime import datetime

print("Content-Type: application/json\n")

response = {
    "title": "Hello, Python!",
    "heading": "Hello, Python!",
    "message": "This page was generated with the Python programming language",
    "time": datetime.now().strftime("%a %b %d %H:%M:%S %Y"),
    "IP": os.environ.get(
        "HTTP_X_FORWARDED_FOR",
        os.environ.get("REMOTE_ADDR", "unknown")
    ).split(",")[0]
}

print(json.dumps(response))
