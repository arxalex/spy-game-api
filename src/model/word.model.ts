export interface WordDTO {
    id?: number;
    setid?: number;
    word?: string;
}

export class Word {
    id?: number;
    setid?: number;
    word?: string;

    constructor(data?: Partial<WordDTO>) {
        if (data) {
            this.id = data.id;
            this.setid = data.setid;
            this.word = data.word;
        }
    }

    static fromDTO(dto: WordDTO | null): Word | null {
        if (!dto) {
            return null;
        }
        return new Word(dto);
    }

    static fromDTOs(dtos: Record<string, WordDTO>): Record<string, Word> {
        const words: Record<string, Word> = {};
        for (const [key, value] of Object.entries(dtos)) {
            words[key] = this.fromDTO(value)!;
        }
        return words;
    }

    toDTO(): WordDTO {
        return {
            id: this.id,
            setid: this.setid,
            word: this.word
        };
    }

    static readonly keys: (keyof WordDTO)[] = ['id', 'setid', 'word'];
}