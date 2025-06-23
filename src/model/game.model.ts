export interface GameDTO {
    id?: number;
    pass?: string;
    setid?: number;
    started?: boolean;
    wordid?: number;
    stoptime?: number;
    infinitemode?: boolean;
    duration?: number;
}

export class Game {
    id?: number;
    pass?: string;
    setid?: number;
    started?: boolean;
    wordid?: number;
    stoptime?: number;
    infinitemode?: boolean;
    duration?: number;

    constructor(data?: Partial<GameDTO>) {
        if (data) {
            this.id = data.id;
            this.pass = data.pass;
            this.setid = data.setid;
            this.started = data.started;
            this.wordid = data.wordid;
            this.stoptime = data.stoptime;
            this.infinitemode = data.infinitemode;
            this.duration = data.duration;
        }
    }

    static fromDTO(dto: GameDTO | null): Game | null {
        if (!dto) {
            return null;
        }
        return new Game(dto);
    }

    toDTO(): GameDTO {
        return {
            id: this.id,
            pass: this.pass,
            setid: this.setid,
            started: this.started,
            wordid: this.wordid,
            stoptime: this.stoptime,
            infinitemode: this.infinitemode,
            duration: this.duration
        };
    }

    static readonly keys: (keyof GameDTO)[] = ['id', 'pass', 'setid', 'started', 'wordid', 'stoptime', 'infinitemode', 'duration'];
}