package main

import (
	"fmt"
	"io"
	"net/url"
	"os"
	"path/filepath"
	"strings"
	"crypto/rand"
	"encoding/hex"
)

const STATE_DIR = "/tmp/hw2_go_state"

/* ---------- Helpers ---------- */

func ensureStateDir() {
	_ = os.MkdirAll(STATE_DIR, 0700)
}

func parseCookies() map[string]string {
	cookies := map[string]string{}
	raw := os.Getenv("HTTP_COOKIE")
	for _, c := range strings.Split(raw, ";") {
		c = strings.TrimSpace(c)
		if strings.Contains(c, "=") {
			parts := strings.SplitN(c, "=", 2)
			v, _ := url.QueryUnescape(parts[1])
			cookies[parts[0]] = v
		}
	}
	return cookies
}

func setCookie(name, value string) {
	fmt.Printf(
		"Set-Cookie: %s=%s; Path=/; HttpOnly; SameSite=Lax\n",
		name,
		url.QueryEscape(value),
	)
}

func redirect(loc string) {
	fmt.Println("Status: 302 Found")
	fmt.Println("Location:", loc)
	fmt.Println()
	os.Exit(0)
}

func newSID() string {
	b := make([]byte, 16)
	_, _ = rand.Read(b)
	return hex.EncodeToString(b)
}

func getOrCreateSID() string {
	ensureStateDir()
	cookies := parseCookies()
	sid := cookies["hw2sid_go"]

	if sid == "" {
		sid = newSID()
		setCookie("hw2sid_go", sid)
	}

	return sid
}

func statePath(sid string) string {
	return filepath.Join(STATE_DIR, sid)
}

func readState(sid string) string {
	data, err := os.ReadFile(statePath(sid))
	if err != nil {
		return ""
	}
	return string(data)
}

func writeState(sid, value string) {
	_ = os.WriteFile(statePath(sid), []byte(value), 0600)
}

func clearState(sid string) {
	_ = os.Remove(statePath(sid))
}

/* ---------- Main ---------- */

func main() {
	method := os.Getenv("REQUEST_METHOD")
	query := os.Getenv("QUERY_STRING")

	sid := getOrCreateSID()

	// RESET
	if strings.Contains(query, "reset=true") {
		clearState(sid)
		redirect("state-view.html")
	}

	// SAVE
	if method == "POST" {
		body, _ := io.ReadAll(os.Stdin)
		data, _ := url.ParseQuery(string(body))
		value := strings.TrimSpace(data.Get("value"))
		writeState(sid, value)
		redirect("state-view.html")
	}

	// VIEW
	value := readState(sid)

	fmt.Println("Content-Type: text/html; charset=utf-8")
	fmt.Println()
	fmt.Println("<!DOCTYPE html>")
	fmt.Println("<html><head><title>Go State View</title></head><body>")
	fmt.Println("<h1>Go State – View</h1>")

	if value == "" {
		fmt.Println("<p><b>No value saved.</b></p>")
	} else {
		fmt.Printf("<p>Saved Value: <b>%s</b></p>\n", value)
	}

	fmt.Println(`<a href="state-input.html">Edit</a><br>`)
	fmt.Println(`<a href="state-go.cgi?reset=true">Clear</a>`)
	fmt.Println("</body></html>")
}
