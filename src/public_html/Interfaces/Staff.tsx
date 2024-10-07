export default interface Interface_Staff{
    id: number,
    username: string,
    emailResetCode: string,
    emailResetExpires: number,
    avatarLink: number,
    phash: string,
    lhash: string,
    psalt: string,
    ownerLevel: boolean
}
export const Default_Staff: Interface_Staff = {
    id: 0,
    username: "",
    emailResetCode: "",
    emailResetExpires: 0,
    avatarLink: 0,
    phash: "",
    lhash: "",
    psalt: "",
    ownerLevel: true
}