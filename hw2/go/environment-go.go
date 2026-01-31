package main

import (
	"fmt"
	"os"
)

func main() {
	fmt.Println("Content-Type: text/html")
	fmt.Println()

	fmt.Println("<!DOCTYPE html>")
	fmt.Println("<html>")
	fmt.Println("<head><title>Environment Variables</title></head>")
	fmt.Println("<body>")
	fmt.Println("<h1>Environment Variables</h1>")
	fmt.Println("<pre>")

	for _, env := range os.Environ() {
		fmt.Println(env)
	}

	fmt.Println("</pre>")
	fmt.Println("</body>")
	fmt.Println("</html>")
}
