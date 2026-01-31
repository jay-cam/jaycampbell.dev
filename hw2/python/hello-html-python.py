#!/usr/bin/env python3
import os
from datetime import datetime

print("Content-Type: text/html\n")

ip = os.environ.get(
        "HTTP_X_FORWARDED_FOR",
        os.environ.get("REMOTE_ADDR", "unknown")
    ).split(",")[0]
now = datetime.now().strftime("%a %b %d %H:%M:%S %Y")

print(f"""<!DOCTYPE html>
<html>
<head>
    <title>Hello HTML World</title>
</head>
<body>
    <h1>Hello HTML World</h1>
    <p>Hello from Jay Campbell</p>
    <p>This page was generated with the Python programming language</p>
    <p>This program was generated at: {now}</p>
    <p>Your current IP Address is: {ip}</p>
</body>
</html>
""")
