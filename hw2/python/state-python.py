#!/usr/bin/env python3
import os
import sys
import urllib.parse
import uuid

STATE_DIR = "/tmp/hw2_python_state"

# ---------------- Helpers ----------------

def ensure_state_dir():
    if not os.path.isdir(STATE_DIR):
        os.makedirs(STATE_DIR, exist_ok=True)

def parse_cookies():
    cookies = {}
    raw = os.environ.get("HTTP_COOKIE", "")
    for c in raw.split(";"):
        if "=" in c:
            k, v = c.strip().split("=", 1)
            cookies[k] = urllib.parse.unquote(v)
    return cookies

def set_cookie(name, value):
    print(
        f"Set-Cookie: {name}={urllib.parse.quote(value)}; "
        "Path=/; HttpOnly; SameSite=Lax"
    )

def clear_cookie(name):
    print(
        f"Set-Cookie: {name}=; "
        "Path=/; Max-Age=0; HttpOnly; SameSite=Lax"
    )

def redirect(location):
    print("Status: 302 Found")
    print(f"Location: {location}")
    print()
    sys.exit(0)

def get_or_create_sid():
    cookies = parse_cookies()
    sid = cookies.get("hw2sid_python")

    ensure_state_dir()

    if not sid or not os.path.exists(os.path.join(STATE_DIR, sid)):
        sid = str(uuid.uuid4())
        open(os.path.join(STATE_DIR, sid), "w").close()
        set_cookie("hw2sid_python", sid)

    return sid

def read_state(sid):
    path = os.path.join(STATE_DIR, sid)
    if os.path.exists(path):
        with open(path, "r") as f:
            return f.read()
    return ""

def write_state(sid, value):
    with open(os.path.join(STATE_DIR, sid), "w") as f:
        f.write(value)

def clear_state(sid):
    path = os.path.join(STATE_DIR, sid)
    if os.path.exists(path):
        os.remove(path)

# ---------------- Main ----------------

method = os.environ.get("REQUEST_METHOD", "GET")
query = urllib.parse.parse_qs(os.environ.get("QUERY_STRING", ""))

sid = get_or_create_sid()

# Reset state
if query.get("reset") == ["true"]:
    clear_state(sid)
    redirect("state-view.html")

# Save state
if method == "POST":
    length = int(os.environ.get("CONTENT_LENGTH", 0))
    body = sys.stdin.read(length)
    data = urllib.parse.parse_qs(body)
    message = data.get("message", [""])[0].strip()

    write_state(sid, message)
    redirect("state-view.html")

# View state
message = read_state(sid)

print("Content-Type: text/html; charset=utf-8")
print()
print("<!DOCTYPE html>")
print("<html><head><title>Python State View</title></head><body>")
print("<h1>Python State – View</h1>")

if message:
    print(f"<p>Saved Message: <b>{message}</b></p>")
else:
    print("<p><b>No message saved.</b></p>")

print('<a href="state-input.html">Edit</a><br>')
print('<a href="state-python.py?reset=true">Clear</a>')
print("</body></html>")
