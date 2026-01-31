package main

import (
	"encoding/json"
	"fmt"
	"io"
	"net/url"
	"os"
	"strings"
	"time"
)

func main() {
	fmt.Println("Content-Type: application/json")
	fmt.Println()

	method := os.Getenv("REQUEST_METHOD")
	queryString := os.Getenv("QUERY_STRING")
	contentType := os.Getenv("CONTENT_TYPE")

	// -------- PARSE QUERY --------
	query := map[string][]string{}
	if queryString != "" {
		query, _ = url.ParseQuery(queryString)
	}

	// -------- PARSE BODY --------
	var body interface{} = nil

	if method != "GET" {
		raw, _ := io.ReadAll(os.Stdin)
		if len(raw) > 0 {
			if strings.Contains(contentType, "application/json") {
				var parsed interface{}
				if err := json.Unmarshal(raw, &parsed); err == nil {
					body = parsed
				} else {
					body = string(raw)
				}
			} else {
				parsed, _ := url.ParseQuery(string(raw))
				body = parsed
			}
		}
	}

// -------- RESPONSE --------
ip := os.Getenv("HTTP_X_FORWARDED_FOR")
if ip == "" {
	ip = os.Getenv("REMOTE_ADDR")
}

resp := map[string]interface{}{
	"method":     method,
	"hostname":   os.Getenv("SERVER_NAME"),
	"time":       time.Now().Format("Mon Jan 02 15:04:05 2006"),
	"ip":         ip,
	"user_agent": os.Getenv("HTTP_USER_AGENT"),
	"query":      query,
	"body":       body,
}

json.NewEncoder(os.Stdout).Encode(resp)

}