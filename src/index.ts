import { Hono } from 'hono';
import { GameService } from './services/game.service';
import { SetService } from './services/set.service';
import { UserService } from './services/user.service';
import { Game } from './model/game.model';
import { Set } from './model/set.model';
import { User } from './model/user.model';
import { Word } from './model/word.model';
import { D1Database } from "@cloudflare/workers-types";

type Env = {
    DB: D1Database;
};

type Variables = {
    userService: UserService,
    setService: SetService,
    gameService: GameService
};

const app = new Hono<{ Bindings: Env; Variables: Variables }>();

app.use('*', async (c, next) => {
    const userService = new UserService(c.env.DB);
    const setService = new SetService(c.env.DB, userService);
    const gameService = new GameService(c.env.DB, userService, setService);

    c.set('userService', userService);
    c.set('setService', setService);
    c.set('gameService', gameService);

    await next();
});

app.post('/api/spy/controllers/user', async (c) => {
    const { method, user } = await c.req.json();
    const userService = c.get('userService');

    switch (method) {
        case 'generateUser':
            return c.json(await userService.generateUser(user.name));
        case 'isUserExists':
            return c.json(await userService.isUserExists(new User(user)));
        case 'getUserInfo':
            return c.json(await userService.getUserInfo(new User(user)));
        case 'changeName':
            return c.json(await userService.changeName(new User(user)));
        default:
            return c.json(null);
    }
});

app.post('/api/spy/controllers/set', async (c) => {
    const { method, set, user, words } = await c.req.json();
    const setService = c.get('setService');

    switch (method) {
        case 'getSet':
            return c.json(await setService.getSet(set.id));
        case 'createSet':
            return c.json(await setService.createSet(set.name, new User(user)));
        case 'updateSet':
            return c.json(await setService.updateSet(
                new Set(set),
                words.map((w: any) => new Word(w)),
                new User(user)
            ));
        case 'deleteSet':
            return c.json(await setService.deleteSet(set.id, new User(user)));
        case 'getList':
            return c.json(await setService.getList(new User(user)));
        case 'getListPublic':
            return c.json(await setService.getListPublic());
        default:
            return c.json(null);
    }
});

app.post('/api/spy/controllers/game', async (c) => {
    const { method, game, user, userId } = await c.req.json();
    const gameService = c.get('gameService');

    switch (method) {
        case 'getGameInfo':
            return c.json(await gameService.getGameInfo(new Game(game), new User(user)));
        case 'generateGame':
            return c.json(await gameService.generateGame(new User(user)));
        case 'joinGame':
            return c.json(await gameService.joinGame(new Game(game), new User(user)));
        case 'quitFromGame':
            return c.json(await gameService.quitFromGame(new Game(game), new User(user)));
        case 'startGame':
            return c.json(await gameService.startGame(new Game(game), new User(user)));
        case 'stopGame':
            return c.json(await gameService.stopGame(new Game(game), new User(user)));
        case 'kickUser':
            return c.json(await gameService.kickUser(new Game(game), new User(user), userId));
        case 'changeMode':
            return c.json(await gameService.changeMode(new Game(game), new User(user)));
        case 'isAdmin':
            return c.json(await gameService.isAdmin(new Game(game), new User(user)));
        case 'getUsers':
            return c.json(await gameService.getUsers(new Game(game), new User(user)));
        default:
            return c.json(null);
    }
});

export default app;