const CAPS_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
const ALL_CHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

export function generateRandomString(length: number = 6, onlyCaps: boolean = false): string {
    const characters = onlyCaps ? CAPS_CHARS : ALL_CHARS;
    const charactersLength = characters.length;
    let randomString = '';

    for (let i = 0; i < length; i++) {
        randomString += characters.charAt(Math.floor(Math.random() * charactersLength));
    }

    return randomString;
}