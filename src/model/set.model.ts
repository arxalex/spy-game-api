export interface SetDTO {
    id?: number;
    name?: string;
    userid?: number;
}

export class Set {
    id?: number;
    name?: string;
    userid?: number;

    constructor(data?: Partial<SetDTO>) {
        if (data) {
            this.id = data.id;
            this.name = data.name;
            this.userid = data.userid;
        }
    }

    static fromDTO(dto: SetDTO | null): Set | null {
        if (!dto) {
            return null;
        }
        return new Set(dto);
    }

    toDTO(): SetDTO {
        return {
            id: this.id,
            name: this.name,
            userid: this.userid
        };
    }

    static readonly keys: (keyof SetDTO)[] = ['id', 'name', 'userid'];
}