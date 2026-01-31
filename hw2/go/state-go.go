package main

import (
	"fmt"
	"io"
	"net/url"
	"os"
	"strings"
)

func getCookie(name string) string {
	cookie := os.Getenv("HTTP_COOKIE")
	for _, c := range strings.Split(cookie, ";") {
		c = strings.TrimSpace(c)
		if strings.HasPrefix(c, name+"=") {
			v, _ := url.QueryUnescape(strings.TrimPrefix(c, name+"="))
			return v
		}
	}
	return ""
}

func setCookie(name, value string) {
	fmt.Printf(
		"Set-Cookie: %s=%s; Path=/; HttpOnly; SameSite=Lax\n",
		name,
		url.QueryEscape(value),
	)
}

func clearCookie(name string) {
	fmt.Printf(
		"Set-Cookie: %s=; Path=/; Max-Age=0; HttpOnly; SameSite=Lax\n",
		name,
	)
}

func redirect(loc string) {
	fmt.Println("Status: 302 Found")
	fmt.Println("Location:", loc)
	fmt.Println()
}

func main() {
	method := os.Getenv("REQUEST_METHOD")
	query := os.Getenv("QUERY_STRING")

	// RESET
	if strings.Contains(query, "reset=true") {
		clearCookie("saved_value")
		redirect("state-view.html")
		return
	}

	// SAVE
	if method == "POST" {
		body, _ := io.ReadAll(os.Stdin)
		data, _ := url.ParseQuery(string(body))
		value := data.Get("value")

		setCookie("saved_value", value)
		redirect("state-view.html")
		return
	}

	// VIEW
	value := getCookie("saved_value")

	fmt.Println("Content-Type: text/html; charset=utf-8\n")
	fmt.Println("<!DOCTYPE html>")
	fmt.Println("<html><head><title>Go State View</title></head><body>")
	fmt.Println("<h1>Go State – View</h1>")

	if value == "" {
		fmt.Println("<p><b>No value saved.</b></p>")
	} else {
		fmt.Printf("<p>Saved Value: <b>%s</b></p>", value)
	}

	fmt.Println(`<a href="state-input.html">Edit</a><br>`)
	fmt.Println(`<a href="state-go.cgi?reset=true">Clear</a>`)
	fmt.Println("</body></html>")
}
