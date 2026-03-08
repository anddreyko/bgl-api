# Feature: GetUser resolve by name

## Problem

BFF needs to resolve user by nickname (name) from URL like `link.com/user/anddreyko`.
Currently `GET /v1/user/{id}` accepts only UUID. BFF has no way to map name → UUID.

## Solution

Extend existing `GET /v1/user/{id}` to accept both UUID and name.
Handler detects format: UUID → `find()`, otherwise → `findByName()`.
No new endpoints — consistent with existing API.

## Scope

- Domain: add `findByName()` to `Users` interface
- Infrastructure: Doctrine implementation
- Application: handler resolution logic
- OpenAPI: update parameter description
