CREATE TABLE IF NOT EXISTS records (
    session TEXT NOT NULL,
    time REAL NOT NULL,
    ts INTEGER NOT NULL,
    result TEXT NOT NULL,
    PRIMARY KEY (ts)
);

CREATE INDEX IF NOT EXISTS avg_idx ON records ("session", "result", "time");
