-- migrate:up
CREATE TABLE dictionary (
    record_id SERIAL PRIMARY KEY,
    "key" VARCHAR(255) UNIQUE NOT NULL,
    picture VARCHAR(255),
    meaning TEXT,
    links JSON
);
CREATE INDEX idx_dictionary_key ON "dictionary"(key);

-- migrate:down
DROP INDEX idx_dictionary_key;
DROP TABLE dictionary;

