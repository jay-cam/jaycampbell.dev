#!/usr/bin/env python3
import os
import sys
import json
import urllib.parse

STATE_FILE = "/tmp/python_fp_state.json"
FP_COOKIE = "fp_id"
MSG_COOKIE = "fp_saved_message"

# ---------------- Helpers ----------------

def read_state():
    if not os.path.exists(STATE_FILE):
        return {}
    with open(STATE_FILE, "r") as f:
        return json.load(f)

def write_state(state):
    with open(STATE_FILE, "w") as f:
        json.dump(state, f)

def get_cookie(name):
    cookies = os.environ.get("HTTP_COOKIE", "")
    for c in cookies.split(";"):
        if "=" in c:
            k, v = c.strip().split("=", 1)
            if k == name:
                return urllib.parse.unquote(v)
    return ""

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

def redirect(loc):
    print("Status: 302 Found")
    print(f"Location: {loc}")
    print()

# ---------------- Main ----------------

method = os.environ.get("REQUEST_METHOD", "GET")
query = urllib.parse.parse_qs(os.environ.get("QUERY_STRING", ""))

# fingerprint arrives via hidden field (POST) or query (GET)
fingerprint = ""
if method == "POST":
    length = int(os.environ.get("CONTENT_LENGTH", 0))
    body = sys.stdin.read(length)
    data = urllib.parse.parse_qs(body)
    fingerprint = data.get("fingerprint", [""])[0]
    message = data.get("message", [""])[0]
else:
    fingerprint = query.get("fingerprint", [""])[0]

state = read_state()

# RESET (cookie only – fingerprint state preserved for demo)
if query.get("reset") == ["true"]:
    clear_cookie(MSG_COOKIE)
    redirect("state-fp-view.html")
    sys.exit(0)

# SAVE
if method == "POST":
    if fingerprint:
        state[fingerprint] = message
        write_state(state)
        set_cookie(FP_COOKIE, fingerprint)
        set_cookie(MSG_COOKIE, message)
    redirect("state-fp-view.html")
    sys.exit(0)

# VIEW
cookie_msg = get_cookie(MSG_COOKIE)
cookie_fp = get_cookie(FP_COOKIE)

decision = "new"
message = ""

if cookie_msg:
    decision = "cookie"
    message = cookie_msg
elif fingerprint and fingerprint in state:
    decision = "fingerprint"
    message = state[fingerprint]
    set_cookie(FP_COOKIE, fingerprint)
    set_cookie(MSG_COOKIE, message)

print("Content-Type: text/html; charset=utf-8\n")
print("<!DOCTYPE html>")
print("<html><head><title>Python FP State View</title></head><body>")
print("<h1>Python State – Fingerprint Demo</h1>")
print(f"<p><b>Fingerprint:</b> {fingerprint or 'none'}</p>")
print(f"<p><b>Cookie present:</b> {'yes' if cookie_msg else 'no'}</p>")
print(f"<p><b>Server decision:</b> {decision}</p>")

if message:
    print(f"<p><b>Saved Message:</b> {message}</p>")
else:
    print("<p><b>No message saved.</b></p>")

print('<a href="state-fp-input.html">Edit</a><br>')
print('<a href="state-python-fp.py?reset=true">Clear Cookie</a>')
print("</body></html>")
