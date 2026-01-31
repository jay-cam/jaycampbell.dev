#!/usr/bin/env python3
import os
import sys
import urllib.parse

# ---------------- Helpers ----------------

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

def redirect(location):
    print(f"Status: 302 Found")
    print(f"Location: {location}")
    print()

# ---------------- Main ----------------

method = os.environ.get("REQUEST_METHOD", "GET")
query = urllib.parse.parse_qs(os.environ.get("QUERY_STRING", ""))

# Reset state
if query.get("reset") == ["true"]:
    clear_cookie("saved_message")
    redirect("state-view.html")
    sys.exit(0)

# Save state
if method == "POST":
    length = int(os.environ.get("CONTENT_LENGTH", 0))
    body = sys.stdin.read(length)
    data = urllib.parse.parse_qs(body)
    message = data.get("message", [""])[0]

    set_cookie("saved_message", message)
    redirect("state-view.html")
    sys.exit(0)

# VIEW state (GET)
message = get_cookie("saved_message")

print("Content-Type: text/html; charset=utf-8\n")
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
