-- migrate:up
CREATE TABLE "user-dictionary" (
    user_id INTEGER,
    record_id INTEGER,
    picture VARCHAR(255),
    meaning TEXT,
    links JSON,
    due TIMESTAMP,
    state INTEGER,
    CONSTRAINT fk_user
        FOREIGN KEY(user_id)
          REFERENCES "user"(id),
    CONSTRAINT fk_record
        FOREIGN KEY(record_id)
          REFERENCES dictionary(record_id)
);

-- migrate:down
ALTER TABLE "user-dictionary"
    DROP CONSTRAINT fk_user;
ALTER TABLE "user-dictionary"
    DROP CONSTRAINT fk_record;
DROP TABLE "user-dictionary";

