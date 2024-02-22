-- migrate:up
CREATE TABLE "user" (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(255),
    lastname VARCHAR(255),
    timezone VARCHAR(64) NOT NULL
);
CREATE INDEX idx_user_email ON "user"(email);

-- migrate:down
DROP INDEX idx_user_email;
DROP TABLE "user";
