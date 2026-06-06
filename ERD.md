# SMS Market Price Alert System - ERD

This document contains the Entity-Relationship Diagram (ERD) for the database. It illustrates the tables and the logical relationships between them.

The relationships shown here are primarily managed through the application logic rather than being enforced by foreign key constraints in the database schema.

## Mermaid ERD

```mermaid
erDiagram
    admins {
        INT id PK
        VARCHAR username
        VARCHAR password_hash
        TIMESTAMP created_at
    }

    farmers {
        INT id PK
        VARCHAR names
        VARCHAR phone_number
        VARCHAR preferred_language
        TIMESTAMP created_at
    }

    prices {
        INT id PK
        VARCHAR market_name
        DECIMAL price
        DATE price_date
        TIMESTAMP created_at
    }

    sms_logs {
        INT id PK
        TEXT message
        VARCHAR status
        TIMESTAMP created_at
    }

    admins ||--o{ farmers : "manages"
    admins ||--o{ prices : "inputs"
    admins ||--o{ sms_logs : "initiates"
    prices }o--o{ sms_logs : "informs"
    farmers }o--o{ sms_logs : "receives"
```

### Relationship Explanations

*   **`admins` to `farmers`**: An admin registers and manages many farmers.
*   **`admins` to `prices`**: An admin inputs many market prices.
*   **`admins` to `sms_logs`**: An admin's action (like a broadcast) initiates the creation of SMS logs.
*   **`prices` to `sms_logs`**: The data from the `prices` table is used to compose the message stored in `sms_logs`.
*   **`prices` to `sms_logs`**: The price data (per kg and per sack) is used to compose the message stored in `sms_logs`.
*   **`farmers` to `sms_logs`**: An SMS log is created for each message sent to a farmer.