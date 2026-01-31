#!/usr/bin/env python3
import os

print("Content-Type: text/html\n")

print("<!DOCTYPE html>")
print("<html><head><title>Environment Variables</title></head><body>")
print("<h1>Environment Variables</h1>")
print("<pre>")

for key, value in os.environ.items():
    print(f"{key} = {value}")

print("</pre>")
print("</body></html>")
