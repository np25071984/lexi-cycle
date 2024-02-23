-- migrate:up
CREATE TYPE state_enum AS ENUM ('state_0', 'state_1', 'state_7', 'state_30', 'state_90', 'state_360');

CREATE TABLE "user-dictionary" (
    user_id INTEGER,
    record_id INTEGER,
    picture VARCHAR(255),
    meaning TEXT,
    links JSON,
    due TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    "state" state_enum NOT NULL,
    CONSTRAINT fk_user
        FOREIGN KEY(user_id)
          REFERENCES "user"(id),
    CONSTRAINT fk_record
        FOREIGN KEY(record_id)
          REFERENCES dictionary(record_id),
    PRIMARY KEY (user_id, record_id)
);

-- migrate:down
ALTER TABLE "user-dictionary"
    DROP CONSTRAINT fk_user;
ALTER TABLE "user-dictionary"
    DROP CONSTRAINT fk_record;
DROP TABLE "user-dictionary";
DROP TYPE state_enum;
