package main

import (
	"fmt"
	"os"
	"time"
)

func main() {
	fmt.Println("Content-Type: text/html")
	fmt.Println()

	ip := os.Getenv("HTTP_X_FORWARDED_FOR")
	if ip == "" {
		ip = os.Getenv("REMOTE_ADDR")
	}

	now := time.Now().Format("Mon Jan 02 15:04:05 2006")

	fmt.Println(`<!DOCTYPE html>
<html>
<head>
	<title>Hello HTML World</title>
</head>
<body>
	<h1>Hello HTML World</h1>
	<p>Hello from Jay Campbell</p>
	<p>This page was generated with the Go programming language</p>
	<p>This program was generated at: ` + now + `</p>
	<p>Your current IP Address is: ` + ip + `</p>
</body>
</html>`)
}
