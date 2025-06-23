export interface UserDTO {
    id?: number;
    pass?: string;
    name?: string;
}

export class User {
    id?: number;
    pass?: string;
    name?: string;

    constructor(data?: Partial<UserDTO>) {
        if (data) {
            this.id = data.id;
            this.pass = data.pass;
            this.name = data.name;
        }
    }

    static fromDTO(dto: UserDTO | null): User | null {
        if (!dto) {
            return null;
        }

        return new User(dto);
    }

    toDTO(): UserDTO {
        return {
            id: this.id,
            pass: this.pass,
            name: this.name
        };
    }

    // List of valid properties for type checking
    static readonly keys: (keyof UserDTO)[] = ['id', 'pass', 'name'];
}