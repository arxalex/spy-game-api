export interface GameUserDTO {
    id?: number;
    userid?: number;
    gameid?: number;
    spy?: boolean;
    owner?: boolean;
}

export class GameUser {
    id?: number;
    userid?: number;
    gameid?: number;
    spy?: boolean;
    owner?: boolean;

    constructor(data?: Partial<GameUserDTO>) {
        if (data) {
            this.id = data.id;
            this.userid = data.userid;
            this.gameid = data.gameid;
            this.spy = data.spy;
            this.owner = data.owner;
        }
    }

    static fromDTO(dto: GameUserDTO | null): GameUser | null {
        if (!dto) {
            return null;
        }
        return new GameUser(dto);
    }

    toDTO(): GameUserDTO {
        return {
            id: this.id,
            userid: this.userid,
            gameid: this.gameid,
            spy: this.spy,
            owner: this.owner
        };
    }

    static readonly keys: (keyof GameUserDTO)[] = ['id', 'userid', 'gameid', 'spy', 'owner'];
}