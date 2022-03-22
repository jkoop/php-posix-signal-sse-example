# PHP POSIX Signal SSE Example

This is a simple example of an [SSE](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events) subscription endpoint using PHP and POSIX signals. The example scenario is a *very* simple chat client.

Notable features include:
+ The PHP script uses POSIX signals to trigger the transmission of new messages.
    + This is a type of non-busy waiting, which is good for performance.

## Requirements
+ POSIX-compliant operating system (Linux, macOS, etc.)
