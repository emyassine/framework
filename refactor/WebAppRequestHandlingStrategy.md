# Web Application Request Handling Strategy

## Overview
A web application must be capable of serving three distinct types of requests:
- **HTTP requests** (standard web pages)
- **API requests** (programmatic endpoints)
- **CLI commands** (local command-line execution)

Each category has different performance considerations and architectural requirements.

## CLI Performance
Command-line execution generally does not present performance issues. Since CLI commands bypass the overhead of HTTP parsing, routing, and network latency, they execute locally with minimal delay.

## API and HTML Requests
For API and HTML requests, performance optimization requires strict segmentation and routing discipline:

- **API Requests**
  All API endpoints should be prefixed with `/api/`. This allows immediate identification and dispatch without unnecessary routing overhead.

- **Static Assets**
  Static resources such as images, CSS, and JavaScript should reside under `/static/` within the public directory. These files should be served directly by the web server (e.g., Nginx or Apache) without invoking the application framework.

- **Dynamic Routes**
  Requests outside `/api/` and `/static/` should be processed by the application router. If a matching route is found, the response is generated; otherwise, a `404 Not Found` response is returned.

## Performance Objective
The goal is to achieve **sub-millisecond response times** for API and HTML requests. While true sub-microsecond responses are not feasible due to inherent network and protocol overhead, a well-structured framework can consistently deliver responses in the microsecond-to-millisecond range.

## Framework Design Principles
To achieve optimal performance, the framework must adhere to the following principles:

1. **Strict Segmentation**
   Clear separation between API, static, and dynamic routes ensures predictable dispatch and minimizes overhead.

2. **Minimal Class Loading**
   The framework must never load unnecessary classes. Class loading should be demand-driven, ensuring only the components required for the current request are initialized.

3. **Opiniated Architecture**
   The framework should enforce a disciplined structure that prevents accidental coupling and unnecessary complexity. This includes modular design and strict namespace organization.

4. **Enterprise Readiness**
   The architecture must be designed for enterprise-grade scenarios, ensuring scalability, maintainability, and predictable performance under load.

## Conclusion
By enforcing strict request segmentation, minimizing class loading, and maintaining an opiniated modular architecture, a web application can achieve high performance across HTTP, API, and CLI contexts. This strategy ensures that the framework remains lean, efficient, and suitable for enterprise deployment.
