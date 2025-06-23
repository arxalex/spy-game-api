import { D1Database } from '@cloudflare/workers-types';
import { Game } from '../model/game.model';
import { GameUser } from '../model/gameuser.model';
import { User } from '../model/user.model';
import { generateRandomString } from '../utils/string.util';
import { UserService } from './user.service';
import { SetService } from './set.service';

export class GameService {
    private readonly db: D1Database;
    private readonly userService: UserService;
    private readonly setService: SetService;

    constructor(db: D1Database, userService: UserService, setService: SetService) {
        this.db = db;
        this.userService = userService;
        this.setService = setService;
    }

    async generateGame(user: User): Promise<Game | null> {
        if (await this.userService.isUserExists(user)) {
            const game = new Game({
                pass: generateRandomString(6, true),
                started: false,
                infinitemode: false
            });

            const result = await this.db
                .prepare('INSERT INTO games (pass) VALUES (?) RETURNING id')
                .bind(game.pass)
                .first<{ id: number }>();

            game.id = result!.id;

            await this.db
                .prepare('INSERT INTO game_users (userid, gameid, owner) VALUES (?, ?, ?)')
                .bind(user.id!, game.id, true)
                .run();

            return game;
        }
        return null;
    }

    async getGameInfo(game: Game, user: User): Promise<Game | null> {
        if (await this.isUserInGame(game.id!, user)) {
            const gameFromDb = await this.db
                .prepare('SELECT * FROM games WHERE id = ?')
                .bind(game.id)
                .first<Game>();

            if (!gameFromDb) return null;

            if (await this.isSpy(game, user)) {
                gameFromDb.wordid = undefined;
            }

            if (!gameFromDb.infinitemode && gameFromDb.stoptime && gameFromDb.stoptime <= Date.now() / 1000) {
                await this.stopGameSuper(game);
                return await this.db
                    .prepare('SELECT * FROM games WHERE id = ?')
                    .bind(game.id)
                    .first<Game>();
            }

            return Game.fromDTO(gameFromDb);
        }
        return null;
    }

    async joinGame(game: Game, user: User): Promise<boolean> {
        if (!await this.isUserInGame(game.id!, user)) {
            if (!await this.isGameStarted(game)) {
                await this.db
                    .prepare('INSERT INTO game_users (userid, gameid) VALUES (?, ?)')
                    .bind(user.id!, game.id!)
                    .run();
                return true;
            }
            return false;
        }
        return true;
    }

    async quitFromGame(game: Game, user: User): Promise<boolean> {
        if (await this.isUserInGame(game.id!, user) && !await this.isGameStarted(game)) {
            const gameUser = await this.db
                .prepare('SELECT id FROM game_users WHERE userid = ? AND gameid = ?')
                .bind(user.id, game.id)
                .first<{ id: number }>();

            if (gameUser) {
                await this.db
                    .prepare('DELETE FROM game_users WHERE id = ?')
                    .bind(gameUser.id)
                    .run();
                return true;
            }
        }
        return false;
    }

    async startGame(game: Game, user: User): Promise<boolean> {
        if (!await this.isGameStarted(game) && await this.isUserGameOwner(game.id!, user)) {
            const gameFromDb = await this.db
                .prepare('SELECT * FROM games WHERE id = ?')
                .bind(game.id)
                .first<Game>();

            if (!gameFromDb) return false;

            const setAndWords = await this.setService.getSet(gameFromDb.setid!);
            const randomWord = await this.setService.getRandomWord(setAndWords);

            const stoptime = !gameFromDb.infinitemode ? Math.floor(Date.now() / 1000) + gameFromDb.duration! : null;

            await this.db
                .prepare('UPDATE games SET started = ?, wordid = ?, stoptime = ? WHERE id = ?')
                .bind(true, randomWord.id, stoptime, game.id)
                .run();

            const gameUsers = await this.db
                .prepare('SELECT * FROM game_users WHERE gameid = ? AND owner = 0')
                .bind(game.id)
                .all<GameUser>();

            const nextSpy = Math.floor(Math.random() * gameUsers.results.length);

            for (let i = 0; i < gameUsers.results.length; i++) {
                const isSpy = i === nextSpy;
                await this.db
                    .prepare('UPDATE game_users SET spy = ? WHERE id = ?')
                    .bind(isSpy, gameUsers.results[i].id)
                    .run();
            }

            return true;
        }
        return false;
    }


    async stopGame(game: Game, user: User): Promise<boolean> {
        if (await this.isGameStarted(game) && await this.isUserGameOwner(game.id!, user)) {
            await this.db
                .prepare('UPDATE games SET started = ?, stoptime = ? WHERE id = ?')
                .bind(false, null, game.id)
                .run();
            return true;
        }
        return false;
    }

    async kickUser(game: Game, owner: User, userId: number): Promise<boolean> {
        if (await this.isUserGameOwner(game.id!, owner)) {
            const kickedUser = await this.userService.getUser(userId);
            if (kickedUser) {
                return await this.quitFromGame(game, kickedUser);
            }
        }
        return false;
    }

    async changeMode(game: Game, user: User): Promise<boolean> {
        if (!await this.isGameStarted(game) && await this.isUserGameOwner(game.id!, user)) {
            await this.db
                .prepare('UPDATE games SET setid = ?, duration = ?, infinitemode = ? WHERE id = ?')
                .bind(game.setid, game.duration, game.infinitemode, game.id)
                .run();
            return true;
        }
        return false;
    }


    async getUsers(game: Game, user: User): Promise<User[]> {
        if (await this.isUserGameOwner(game.id!, user)) {
            const gameUsers = await this.db
                .prepare('SELECT userid FROM game_users WHERE gameid = ? AND owner = 0')
                .bind(game.id)
                .all<{ userid: number }>();

            const userIds = gameUsers.results.map(gu => gu.userid);
            return await this.userService.getUsers(userIds);
        }
        return [];
    }

    async isAdmin(game: Game, user: User): Promise<boolean> {
        return await this.isUserGameOwner(game.id!, user);
    }

    private async isUserInGame(gameId: number, user: User): Promise<boolean> {
        if (await this.userService.isUserExists(user)) {
            const gameUser = await this.db
                .prepare('SELECT * FROM game_users WHERE userid = ? AND gameid = ?')
                .bind(user.id, gameId)
                .first();
            return gameUser !== null;
        }
        return false;
    }

    private async isGameStarted(game: Game): Promise<boolean> {
        const gameFromDb = await this.db
            .prepare('SELECT started FROM games WHERE id = ? AND pass = ?')
            .bind(game.id, game.pass)
            .first<{ started: boolean }>();
        return gameFromDb?.started ?? false;
    }

    private async isUserGameOwner(gameId: number, user: User): Promise<boolean> {
        if (await this.userService.isUserExists(user)) {
            const gameUser = await this.db
                .prepare('SELECT owner FROM game_users WHERE userid = ? AND gameid = ?')
                .bind(user.id, gameId)
                .first<{ owner: boolean }>();
            return gameUser?.owner ?? false;
        }
        return false;
    }

    private async isSpy(game: Game, user: User): Promise<boolean> {
        if (await this.isUserInGame(game.id!, user) && await this.isGameStarted(game)) {
            const gameUser = await this.db
                .prepare('SELECT spy FROM game_users WHERE userid = ? AND gameid = ?')
                .bind(user.id, game.id)
                .first<{ spy: boolean }>();
            return gameUser?.spy ?? false;
        }
        return false;
    }

    private async stopGameSuper(game: Game): Promise<boolean> {
        if (await this.isGameStarted(game)) {
            await this.db
                .prepare('UPDATE games SET started = ?, stoptime = ? WHERE id = ?')
                .bind(false, null, game.id)
                .run();
            return true;
        }
        return false;
    }
}