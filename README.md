# LaraNotes API

LaraNotes API is a small, API-only Laravel application built as a **clean, idiomatic reference project**.

It demonstrates how I apply the same architectural principles shown in *LaraNote*—Laravel conventions, clear responsibility boundaries, and restraint around abstractions—in an API context.

This repository is intended to be a **companion reference** to LaraNote, not a progression in complexity.

---

## Purpose

This project exists to demonstrate:

- Idiomatic Laravel API design
- Token-based authentication using Laravel Sanctum
- Policy-driven authorization
- Form Request–based validation and authorization
- Clean JSON responses using API Resources
- Behavior-focused feature and policy tests

The goal is correctness and clarity, not feature breadth.

---

## Core Concept

### What is a Note?

A **Note** is a user-owned resource representing a short piece of text content.

A note:
- belongs to exactly one user
- has a title and body
- can be archived
- is never shared with other users

---

## Ownership & Authorization Rules

- Every note belongs to exactly one authenticated user
- Users may only view, update, or archive notes they own
- Users may never access notes owned by other users

All ownership and access rules are enforced via **Laravel policies**.

---

## Allowed Actions

An authenticated user may:

- create a note
- list their own notes
- update a note they own
- archive a note they own

---

## Forbidden Actions

A user may **not**:

- view another user’s notes
- update another user’s notes
- archive another user’s notes
- exceed the maximum allowed number of notes

Authorization failures return `403 Forbidden`.

Validation failures return `422 Unprocessable Entity`.

---

## Note Limits

- A user may create up to a fixed maximum number of notes
- The limit is enforced at the **authorization layer**
- Both active and archived notes count toward the limit

Once created, a note always counts toward the limit.

---

## Archiving Behavior

- Archiving is a state change, not deletion
- Archived notes remain in the database
- Archived notes are excluded from active listings by default
- Archiving an already archived note is a no-op

There is no unarchive functionality.

---

## API Design

- All endpoints return JSON
- All write operations require authentication
- Authorization is enforced before persistence
- Unauthorized users never receive access to another user’s data

---

## Data Shape (Conceptual)

A note consists of:

- `id`
- `title`
- `body`
- `archived`
- `timestamps`

Responses are shaped using **API Resources**.

---

## Architecture Overview

The application follows standard Laravel structure:

Request → Form Request → Controller → Policy → Model → API Resource

Responsibilities:

- **Controllers**: orchestration only
- **Form Requests**: validation and authorization delegation
- **Policies**: access rules and limits
- **Models**: persistence and simple domain behavior
- **API Resources**: response formatting

No service layer or custom domain abstractions are used.

---

## API Endpoints

Authenticated routes:

- `GET /api/notes` — list active notes
- `POST /api/notes` — create a note
- `PATCH /api/notes/{note}` — update a note
- `POST /api/notes/{note}/archive` — archive a note

---

## Testing Strategy

Tests focus on **observable behavior**, not implementation details.

The test suite includes:

- Policy tests to validate ownership and note limit rules
- Feature tests for API endpoints
- Authentication and authorization scenarios

---

## Non-Goals

This project intentionally does **not** include:

- shared notes or collaboration
- roles or permission systems
- background jobs or queues
- event-driven architecture
- complex filtering or querying
- frontend views

---

## Setup (Optional)

Instructions for local setup will be added once the API surface is complete.

---

## Final Notes

This project favors **clarity over cleverness**.

Any abstraction or structural decision should be justified by actual complexity, not anticipation.

