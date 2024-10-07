export default interface Interface_Avatar{
    id: number,
    avatarUUID: string,
    avatarName: string,
    avatarUid: string,
    lastUsed: number,
    credits: number
}
export const Default_Avatar: Interface_Avatar = {
    id: 0,
    avatarUUID: "",
    avatarName: "",
    avatarUid: "",
    lastUsed: 0,
    credits: 0
}