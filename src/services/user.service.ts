import { D1Database } from '@cloudflare/workers-types';
import { User } from '../model/user.model';
import { generateRandomString } from '../utils/string.util';

export class UserService {
    private readonly db: D1Database;

    constructor(db: D1Database) {
        this.db = db;
    }

    async generateUser(name: string): Promise<User> {
        const user = new User({
            pass: generateRandomString(6, true),
            name: name
        });

        const result = await this.db
            .prepare('INSERT INTO users (pass, name) VALUES (?, ?) RETURNING id')
            .bind(user.pass, user.name)
            .first<{ id: number }>();

        user.id = result!.id;
        return user;
    }

    async isUserExists(user: User): Promise<boolean> {
        const userFromDb = await this.db
            .prepare('SELECT * FROM users WHERE id = ? AND pass = ?')
            .bind(user.id, user.pass)
            .first();
        return userFromDb !== null;
    }

    async getUser(id: number): Promise<User | null> {
        const userFromDb = await this.db
            .prepare('SELECT * FROM users WHERE id = ?')
            .bind(id)
            .first<User>();
        return User.fromDTO(userFromDb);
    }

    async getUsers(ids: number[]): Promise<User[]> {
        if (ids.length === 0) return [];

        const placeholders = ids.map(() => '?').join(',');
        const users = await this.db
            .prepare(`SELECT * FROM users WHERE id IN (${placeholders})`)
            .bind(...ids)
            .all<User>();

        return users.results.map(user => {
            const userObj = User.fromDTO(user)!;
            userObj.pass = '';
            return userObj;
        });
    }

    async getUserInfo(user: User): Promise<User | null> {
        if (await this.isUserExists(user)) {
            return await this.getUser(user.id!);
        }
        return null;
    }

    async changeName(user: User): Promise<boolean> {
        if (await this.isUserExists(user)) {
            await this.db
                .prepare('UPDATE users SET name = ? WHERE id = ?')
                .bind(user.name, user.id)
                .run();
            return true;
        }
        return false;
    }
}