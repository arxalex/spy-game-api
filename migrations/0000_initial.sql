-- Migration file for Spy Game schema

DROP TABLE IF EXISTS games;
CREATE TABLE games (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pass TEXT NOT NULL,
    setid INTEGER NOT NULL DEFAULT 0,
    started INTEGER NOT NULL DEFAULT 0,
    wordid INTEGER NOT NULL DEFAULT 0,
    stoptime INTEGER DEFAULT NULL,
    infinitemode INTEGER NOT NULL DEFAULT 0,
    duration INTEGER NOT NULL DEFAULT 300
);

DROP TABLE IF EXISTS sets;
CREATE TABLE sets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    userid INTEGER NOT NULL
);

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    pass TEXT NOT NULL,
    name TEXT NOT NULL
);

DROP TABLE IF EXISTS game_users;
CREATE TABLE game_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userid INTEGER NOT NULL,
    gameid INTEGER NOT NULL,
    spy INTEGER NOT NULL DEFAULT 0,
    owner INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (userid) REFERENCES users(id),
    FOREIGN KEY (gameid) REFERENCES games(id)
);

DROP TABLE IF EXISTS words;
CREATE TABLE words (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setid INTEGER NOT NULL,
    word TEXT NOT NULL,
    FOREIGN KEY (setid) REFERENCES sets(id)
);

-- Create indexes for better query performance
CREATE INDEX idx_games_setid ON games(setid);
CREATE INDEX idx_games_wordid ON games(wordid);
CREATE INDEX idx_sets_userid ON sets(userid);
CREATE INDEX idx_game_users_gameid ON game_users(gameid);
CREATE INDEX idx_game_users_userid ON game_users(userid);
CREATE INDEX idx_words_setid ON words(setid);