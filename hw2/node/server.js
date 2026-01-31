const express = require("express");
const session = require("express-session");

const app = express();
app.use(express.static("/var/www/jaycampbell.dev/hw2/node"));

// Behind Apache reverse proxy
app.set("trust proxy", 1);

app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// ---------------- SESSION (SERVER-SIDE) ----------------
app.use(
    session({
        name: "hw2sid",
        secret: "replace-this-with-a-random-string",
        resave: false,
        saveUninitialized: false,
        cookie: {
            httpOnly: true,
            sameSite: "lax",
            secure: false
        }
    })
);

// ---------------- HELLO HTML ----------------
app.get("/hello-html-node", (req, res) => {
    const ip = req.ip || "unknown";
    const now = new Date().toString();

    res.set("Content-Type", "text/html");
    res.send(`<!DOCTYPE html>
<html>
<head>
  <title>Hello HTML World</title>
</head>
<body>
  <h1>Hello HTML World</h1>
  <p>Hello from Jay Campbell</p>
  <p>This page was generated with the NodeJS programming language</p>
  <p>This program was generated at: ${now}</p>
  <p>Your current IP Address is: ${ip}</p>
</body>
</html>`);
});

// ---------------- HELLO JSON ----------------
app.get("/hello-json-node", (req, res) => {
    res.json({
        title: "Hello, NodeJS!",
        heading: "Hello, NodeJS!",
        message: "This page was generated with the NodeJS programming language",
        time: new Date().toString(),
        IP: req.ip || "unknown"
    });
});

// ---------------- ENVIRONMENT ----------------
app.get("/environment-node", (req, res) => {
    res.set("Content-Type", "text/html");

    res.send(`<!DOCTYPE html>
<html>
<head>
  <title>Environment Variables</title>
</head>
<body>
  <h1>Environment Variables</h1>
  <pre>${Object.entries(process.env)
            .map(([k, v]) => `${k} = ${v}`)
            .join("\n")}</pre>
</body>
</html>`);
});

// ---------------- STATE SAVE ----------------
app.post("/state-node", (req, res) => {
    const v = (req.body.value ?? "").trim();
    req.session.savedValue = v.length ? v : null;
    res.redirect("/hw2/node/state-view.html");
});

// ---------------- STATE VIEW / RESET ----------------
app.get("/state-node", (req, res) => {
    if (req.query.reset === "true") {
        req.session.savedValue = null;
        return res.redirect("/hw2/node/state-view.html");
    }

    const value = req.session.savedValue;

    res.set("Content-Type", "text/html");
    res.send(`<!DOCTYPE html>
<html>
<head><title>Node State View</title></head>
<body>
  <h1>Node State – View</h1>
  <p>${value ? `Saved Value: <b>${value}</b>` : "<b>No value saved.</b>"}</p>
  <a href="/hw2/node/state-input.html">Edit</a><br>
  <a href="/hw2/node/state-node?reset=true">Clear</a>
</body>
</html>`);
});

// ---------------- ECHO (FIXED) ----------------
app.all("/echo", (req, res) => {
    res.json({
        method: req.method,
        hostname: req.hostname,
        time: new Date().toString(),
        ip: req.headers["x-forwarded-for"]?.split(",")[0] || req.ip,
        user_agent: req.headers["user-agent"] || "unknown",
        query: req.query,
        body: req.body
    });
});

app.listen(3000, () => {
    console.log("Node server running on port 3000");
});
