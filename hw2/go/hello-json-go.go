package main

import (
	"encoding/json"
	"fmt"
	"os"
	"time"
)

func main() {
	fmt.Println("Content-Type: application/json")
	fmt.Println()

	ip := os.Getenv("HTTP_X_FORWARDED_FOR")
	if ip == "" {
		ip = os.Getenv("REMOTE_ADDR")
	}

	resp := map[string]string{
		"title":   "Hello, Go!",
		"heading": "Hello, Go!",
		"message": "This page was generated with the Go programming language",
		"time":    time.Now().Format("Mon Jan 02 15:04:05 2006"),
		"IP":      ip,
	}

	json.NewEncoder(os.Stdout).Encode(resp)
}
