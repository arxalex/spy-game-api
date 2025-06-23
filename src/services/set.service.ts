import { D1Database } from '@cloudflare/workers-types';
import { Set } from '../model/set.model';
import { Word } from '../model/word.model';
import { User } from '../model/user.model';
import { UserService } from './user.service';

interface SetAndWords {
    set: Set;
    words: Word[];
}

export class SetService {
    private readonly db: D1Database;
    private readonly userService: UserService;

    constructor(db: D1Database, userService: UserService) {
        this.db = db;
        this.userService = userService;
    }

    async getSet(setId: number): Promise<SetAndWords> {
        const set = await this.db
            .prepare('SELECT * FROM sets WHERE id = ?')
            .bind(setId)
            .first<Set>();

        const words = await this.db
            .prepare('SELECT * FROM words WHERE setid = ?')
            .bind(setId)
            .all<Word>();

        return {
            set: Set.fromDTO(set)!,
            words: words.results.map(word => Word.fromDTO(word)!)
        };
    }

    async createSet(name: string, user: User): Promise<Set | null> {
        if (await this.userService.isUserExists(user)) {
            const set = new Set({
                name: name,
                userid: user.id
            });

            const result = await this.db
                .prepare('INSERT INTO sets (name, userid) VALUES (?, ?) RETURNING id')
                .bind(set.name, set.userid)
                .first<{ id: number }>();

            set.id = result!.id;
            return set;
        }
        return null;
    }

    async updateSet(set: Set, words: Word[], user: User): Promise<boolean> {
        if (await this.isUserOwner(set, user)) {
            await this.db
                .prepare('UPDATE sets SET name = ? WHERE id = ?')
                .bind(set.name, set.id)
                .run();

            const existingWords = await this.db
                .prepare('SELECT * FROM words WHERE setid = ?')
                .bind(set.id)
                .all<Word>();

            const wordIds: number[] = [];

            for (const word of words) {
                if (!word.id) {
                    const result = await this.db
                        .prepare('INSERT INTO words (setid, word) VALUES (?, ?) RETURNING id')
                        .bind(set.id, word.word)
                        .first<{ id: number }>();
                    wordIds.push(result!.id);
                } else if (await this.validateWord(word, set)) {
                    await this.db
                        .prepare('UPDATE words SET word = ? WHERE id = ?')
                        .bind(word.word, word.id)
                        .run();
                    wordIds.push(word.id);
                }
            }

            for (const existingWord of existingWords.results) {
                if (!wordIds.includes(existingWord.id!)) {
                    await this.db
                        .prepare('DELETE FROM words WHERE id = ?')
                        .bind(existingWord.id)
                        .run();
                }
            }

            return true;
        }
        return false;
    }

    async deleteSet(setId: number, user: User): Promise<boolean> {
        const setAndWords = await this.getSet(setId);
        if (await this.isUserOwner(setAndWords.set, user)) {
            await this.db
                .prepare('UPDATE games SET setid = 0 WHERE setid = ?')
                .bind(setId)
                .run();

            await this.db
                .prepare('DELETE FROM words WHERE setid = ?')
                .bind(setId)
                .run();

            await this.db
                .prepare('DELETE FROM sets WHERE id = ?')
                .bind(setId)
                .run();

            return true;
        }
        return false;
    }

    async isUserOwner(set: Set, user: User): Promise<boolean> {
        if (await this.userService.isUserExists(user)) {
            const setFromDb = await this.db
                .prepare('SELECT userid FROM sets WHERE id = ?')
                .bind(set.id)
                .first<Set>();
            return setFromDb?.userid === user.id && set.userid === user.id;
        }
        return false;
    }

    async getWord(id: number): Promise<Word | null> {
        const word = await this.db
            .prepare('SELECT * FROM words WHERE id = ?')
            .bind(id)
            .first<Word>();
        return Word.fromDTO(word);
    }

    async validateWord(word: Word, set: Set): Promise<boolean> {
        const wordFromDb = await this.getWord(word.id!);
        return wordFromDb?.setid === word.setid && set.id === word.setid;
    }

    async getRandomWord(setAndWords: SetAndWords): Promise<Word> {
        const randomIndex = Math.floor(Math.random() * setAndWords.words.length);
        return setAndWords.words[randomIndex];
    }

    async getList(user: User): Promise<Set[]> {
        if (await this.userService.isUserExists(user)) {
            const sets = await this.db
                .prepare('SELECT * FROM sets WHERE userid = ?')
                .bind(user.id)
                .all<Set>();
            return sets.results.map(set => Set.fromDTO(set)!);
        }
        return [];
    }

    async getListPublic(): Promise<Set[]> {
        const sets = await this.db
            .prepare('SELECT * FROM sets WHERE userid = 0')
            .all<Set>();
        return sets.results.map(set => Set.fromDTO(set)!);
    }
}