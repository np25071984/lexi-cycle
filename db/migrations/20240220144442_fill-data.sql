-- migrate:up

INSERT INTO dictionary("key", meaning, links) VALUES (
    'mesmerizing',
    'Very attractive, in a mysterious way, making you want to keep looking

* He had the most **mesmerizing** blue eyes',
    '[{"title": "Cambridge Dictionary", "link": "https://dictionary.cambridge.org/us/dictionary/english/mesmerizing"}, {"title":"YouGlish", "link": "https://youglish.com/pronounce/mesmerizing/english?"}]'
);

INSERT INTO dictionary("key", meaning, links) VALUES (
    'come across',
    '## Behave

To behave in a way that makes people believe that you have a particular characteristic

* She **comes across** really well (= creates a positive image) on television.
* He **comes across as** kind of of a bore in an interview

## Express

If an idea or emotion **comes across** in writing, a movie, music, or when someone is speaking, it is expressed clearly and people notice it.

* What **comes across** in his later poetry is a great sense of sadness.

## Seem

* He **comes across as** a bit of a bore
* We don''t mean to be vague, but I know that''s how it **comes across**.',
    '[{"title": "Cambridge Dictionary", "link": "https://dictionary.cambridge.org/us/dictionary/english/come-across"}, {"title":"YouGlish", "link": "https://youglish.com/pronounce/come%20across/english?"}]'
);

INSERT INTO dictionary("key", meaning) VALUES (
    'stray <noun>',
    'A domestic animal that is wandering at large or is lost.'
);

INSERT INTO dictionary("key", meaning) VALUES (
    'shoddy',
    'Badly and carelessly made, using low quality materials.

* The furniture is shoddy and cheap.

Showing little respect, thought, or care.

* They refused him sick pay when he had the flu, which is a shoddy way to treat an employee.
'
);

INSERT INTO dictionary("key", meaning) VALUES (
    'exercising cleans the crazy out of you',
    'After exercising I feel more calm and relaxed'
);

INSERT INTO dictionary("key", meaning, links) VALUES (
    'genius',
    '## Noun

Refers to a person possessing extraordinary intelligence. A genius is an individual who displays remarkable intellectual ability and has an exceptional mental capacity.

Various organizations have metrics that they use to classify geniuses with. However, most people don''t use a scientifically precise definition.

Instead, most people use the word genius to refer to someone with exceptional intellectual ability in at least one field, especially someone with high achievements in their field. There are scientific and mathematical geniuses as well as creative and artistic geniuses.

* Albert Einstein and Marie Curie were geniuses.

## Adjective

**Genius** is often used as an adjective to describe a creative or original work or idea. An extraordinary creative process might be referred to as being genius.

If someone reveals a particularly unique, smart, and original idea, you might say they had a genius idea.

* She had a genius idea for a new app.
',
    '[{"title": "ProWritingAid", "link": "https://prowritingaid.com/genius-vs-genious"}]'
);

INSERT INTO dictionary("key") VALUES ('Rome was not built overnight');

INSERT INTO "user"(email, password, firstname, lastname, timezone)
VALUES ('mail@mail.com', 'secret', 'Lexi', 'Cycle', 'US/Eastern');

INSERT INTO "user-dictionary"(user_id, record_id, "state") VALUES (
    (SELECT id FROM "user" WHERE email = 'mail@mail.com'),
    (SELECT record_id FROM "dictionary" WHERE "key" = 'mesmerizing'),
    'state_90'
);

INSERT INTO "user-dictionary"(user_id, record_id, "state") VALUES (
    (SELECT id FROM "user" WHERE email = 'mail@mail.com'),
    (SELECT record_id FROM "dictionary" WHERE "key" = 'come across'),
    'state_30'
);

INSERT INTO "user-dictionary"(user_id, record_id, "state") VALUES (
    (SELECT id FROM "user" WHERE email = 'mail@mail.com'),
    (SELECT record_id FROM "dictionary" WHERE "key" = 'stray <noun>'),
    'state_7'
);

INSERT INTO "user-dictionary"(user_id, record_id, "state") VALUES (
    (SELECT id FROM "user" WHERE email = 'mail@mail.com'),
    (SELECT record_id FROM "dictionary" WHERE "key" = 'shoddy'),
    'state_1'
);

INSERT INTO "user-dictionary"(user_id, record_id, "state") VALUES (
    (SELECT id FROM "user" WHERE email = 'mail@mail.com'),
    (SELECT record_id FROM "dictionary" WHERE "key" = 'Rome was not built overnight'),
    'state_0'
);

-- migrate:down
DELETE FROM "user-dictionary";
DELETE FROM "user";
DELETE FROM dictionary;

